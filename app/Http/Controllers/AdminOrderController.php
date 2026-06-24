<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use Illuminate\Http\Request;
use ZipArchive;
use Illuminate\Support\Facades\Storage;

class AdminOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) // Inject Request
    {
	$user = auth()->user();
	$role = $user->role;

	// Admin-level roles (admin / supreme / superadmin) all see the full
	// orders list. Previously the 'admin' role was restricted to orders
	// requested by 'admin' or 'promoter' users, which meant orders placed
	// by promoter_managers / sub_promoters disappeared from the listing,
	// making the status filter and the sold-tickets display look broken
	// for the SUPREME admin account (which is seeded with role 'admin').
	$allowedRequestedByUserIds = null;

	$query = TicketOrder::with([
	    'items.ticketType',
	    'orderedBy',
	    'requestedBy'
	]);

	// Hide private (supreme-admin) sales from the admin orders list.
	// Supreme-admins see only their own private sales via the /promoter/*
	// routes (promoter.orders.index).
	$query->publicOnly();

	// Apply role-based filtering
	if ($allowedRequestedByUserIds !== null) {
	    $query->whereIn('requested_by', $allowedRequestedByUserIds);
	}

	// Search functionality
	if ($request->filled('search')) {
	    $searchTerm = $request->input('search');
	    $query->where(function ($q) use ($searchTerm) {
		$q->where('id', 'LIKE', "%{$searchTerm}%")
		    ->orWhere('email', 'LIKE', "%{$searchTerm}%")
		    ->orWhereHas('orderedBy', function ($subQ) use ($searchTerm) {
			$subQ->where('name', 'LIKE', "%{$searchTerm}%");
		    })
		    ->orWhereHas('requestedBy', function ($subQ) use ($searchTerm) {
			$subQ->where('name', 'LIKE', "%{$searchTerm}%");
		    });
	    });
	}

	// Status filter functionality
	if ($request->filled('status_filter')) {
	    $query->where('job_status', $request->input('status_filter'));
	}

	// Per-promoter filter. Lets a supreme admin (or a regular admin) scope
	// the listing to a single promoter's orders — i.e. "show me only what
	// user X sees" or "what X sold this week". Applied AFTER role filtering
	// so the dropdown can never surface rows the current user is not allowed
	// to see. For regular admins the list is also intersected with the
	// role-based allowed IDs above.
	if ($request->filled('requested_by')) {
	    $requestedBy = (int) $request->input('requested_by');
	    if ($allowedRequestedByUserIds === null || $allowedRequestedByUserIds->contains($requestedBy)) {
	        $query->where('requested_by', $requestedBy);
	    } else {
	        // Disallowed: force an empty result rather than silently widening visibility.
	        $query->whereRaw('1 = 0');
	    }
	}

	// Final result
	$orders = $query->latest()->paginate(15)->withQueryString();

	// Build the dropdown of selectable promoters. For a regular admin we
	// only offer users whose orders they could already see. For supreme
	// admins we offer every user that has ever placed (requested) an order
	// plus active promoters/managers so a freshly-filtered scope is
	// always possible.
	//
	// The filter dropdown is intentionally built from PUBLIC orders only
	// (publicOnly() on the inner query). Listing a supreme-admin as a
	// filter option would let other admins narrow the list down to that
	// user's private sales via the ?requested_by=ID parameter — which the
	// role-based $allowedRequestedByUserIds gate below would then turn
	// into a forced empty result. Better to never offer it as an option.
	if ($allowedRequestedByUserIds === null) {
	    $filterableUsers = \App\Models\User::query()
	        ->where(function ($q) {
	            $q->whereIn('role', ['admin', 'supreme', 'promoter', 'promoter_manager', 'sub_promoter'])
	                ->orWhereIn('id', TicketOrder::query()->publicOnly()->select('requested_by')->distinct());
	        })
	        ->orderBy('name')
	        ->get(['id', 'name', 'email', 'role']);
	} else {
	    $filterableUsers = \App\Models\User::query()
	        ->whereIn('id', $allowedRequestedByUserIds)
	        ->orderBy('name')
	        ->get(['id', 'name', 'email', 'role']);
	}


        return view('pages.admin.orders.index', compact('orders', 'filterableUsers', 'role'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }


    public function updatePayment(Request $request, TicketOrder $order)
    {
        // Private (supreme-admin) sales cannot have their payment edited
        // by another admin.
        if ($order->is_private) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'paid' => 'required|numeric|min:0',
        ]);

        $order->paid = $request->input('paid');
        $order->save();

        return redirect()->back()->with('success', __('alert.payment_amount_updated'));
    }

    public function downloadQRCodes(Request $request, TicketOrder $order)
    {
        // Private (supreme-admin) sales cannot be downloaded by any other
        // admin — they are owned by the seller alone.
        if ($order->is_private) {
            abort(403, 'Unauthorized action.');
        }
        $zip = new ZipArchive();
        $fileName = 'qrcodes_order_' . $order->id . '.zip';

        // Define a directory for creating the zip. Using storage/app/temp is often safer.
        // For this example, we'll ensure the user's chosen public path part.
        $zipDirectory = storage_path("app/temp_zips"); // Or storage_path("app/temp_zips")

        // Ensure the directory exists and is writable
        if (!file_exists($zipDirectory)) {
            mkdir($zipDirectory, 0775, true);
        }
        $zipPath = $zipDirectory . DIRECTORY_SEPARATOR . $fileName;

        // Determine which tickets to process
        $selectedCodes = $request->input('selected_codes'); // This comes from your form
        $ticketsToProcess = collect(); // Initialize an empty collection

        if (is_array($selectedCodes) && !empty($selectedCodes)) {
            $ticketsToProcess = $order->tickets()->whereIn('code', $selectedCodes)->get();
            if ($ticketsToProcess->isEmpty()) {
                return back()->with('error', __('alert.ticket_codes_not_found'));
            }
        } else {
            // If no selected_codes, assume download all for this order
            $ticketsToProcess = $order->tickets;
        }

        if ($ticketsToProcess->isEmpty()) {
            return back()->with('error', __('alert.no_tickets_to_process'));
        }

        // Try to create and open the zip file
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $filesAdded = 0;

            foreach ($ticketsToProcess as $ticket) {
                $individualQrPath = storage_path("app/public/{$ticket->qr_code_path}");

                if (file_exists($individualQrPath)) {
                    $zip->addFile($individualQrPath, basename($individualQrPath)); // e.g., "TICKETCODE1.png"
                    $filesAdded++;
                }
            }

            $zip->close(); // Close the zip archive to finalize it

            // Only attempt to download if files were added and the zip file exists
            if ($filesAdded > 0 && file_exists($zipPath)) {
                return response()->download($zipPath, $fileName)->deleteFileAfterSend(true);
            } else {
                // If no files were added, or zip somehow wasn't created, clean up if an empty zip exists
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return back()->with('error', __('alert.no_qr_codes_found'));
            }
        } else {
            return back()->with('error', __('alert.zip_creation_failed'));
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
