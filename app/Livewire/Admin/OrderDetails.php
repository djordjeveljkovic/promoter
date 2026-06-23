<?php

namespace App\Livewire\Admin;

use App\Jobs\GenerateTicketImagesJob;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\TicketOrder;
use App\Models\Ticket; // Make sure to import your Ticket model

#[Layout('components.layouts.app')]
class OrderDetails extends Component
{
    public TicketOrder $order;
    public $totalPrice;
    public $groupedTickets = [];
    public $paid;
    public $ticketTypeFilter = 'all';
    public $selectedCodes = [];

    public $showPaidInput = false;

    protected $rules = [
        'paid' => 'required|numeric|min:0',
    ];

    protected $successs = [
        'paid.required' => 'The paid amount is required.',
        'paid.numeric' => 'The paid amount must be a number.',
        'paid.min' => 'The paid amount cannot be negative.',
    ];

    public function mount($id)
    {
        // Load order with tickets and their types. Ensure 'is_active' is loaded for tickets.
        $order = TicketOrder::with('tickets.ticketType')->findOrFail($id);

        // Private (supreme-admin) sales are owned by the seller alone — no
        // other admin can open this page or download QR codes from it.
        if ($order->is_private) {
            abort(403, 'Unauthorized action.');
        }

        $this->order = $order;
        $this->paid = $order->paid;
        $this->totalPrice = $order->total;
        $this->groupedTickets = $order->tickets->mapToGroups(function ($ticket) {
            $typeName = optional($ticket->ticketType)->name ?? 'Unknown Type';
            return [$typeName => $ticket];
        });
    }

    public function togglePaidInput()
    {
        $this->showPaidInput = !$this->showPaidInput;
        if (!$this->showPaidInput) {
            $this->paid = $this->order->paid;
            $this->clearValidation('paid');
        }
    }

    public function updatePayment()
    {
        $this->validate();
        $this->order->paid = $this->paid;
        $this->order->save();
        $this->showPaidInput = false;
        session()->flash('success', 'Payment amount updated successfully.');
    }

    public function downloadSelected()
    {
        if (empty($this->selectedCodes)) {
            session()->flash('error', 'Please select at least one ticket to download.');
            return;
        }
        return redirect()->route('admin.orders.downloadQRCodes', [
            'order' => $this->order->id,
            'selected_codes' => $this->selectedCodes,
        ]);
    }

    /**
     * Updates the 'is_active' status for the selected tickets.
     */
    public function updateSelectedTicketsActiveStatus(bool $isActive)
    {
        if (empty($this->selectedCodes)) {
            session()->flash('error', 'No tickets selected to update.');
            return;
        }

        // Assuming $this->selectedCodes contains the 'code' of the tickets.
        // Fetch the IDs of the tickets that belong to the current order and match the selected codes.
        $ticketIdsToUpdate = $this->order->tickets()
                                ->whereIn('code', $this->selectedCodes) // Or 'id' if selectedCodes stores IDs
                                ->pluck('id');

        if ($ticketIdsToUpdate->isEmpty()) {
            session()->flash('error', 'None of the selected ticket codes were found for this order.');
            return;
        }

        // Update the 'is_active' status for the identified tickets.
        // Ensure your App\Models\Ticket model has 'is_active' in its $fillable array.
        Ticket::whereIn('id', $ticketIdsToUpdate)->update(['is_active' => $isActive]);

        // Refresh the order and its tickets to reflect the changes in the component's state.
        $this->order = $this->order->fresh(['tickets.ticketType']);

        // Re-populate groupedTickets
        $this->groupedTickets = $this->order->tickets->mapToGroups(function ($ticket) {
            $typeName = optional($ticket->ticketType)->name ?? 'Unknown Type';
            return [$typeName => $ticket];
        });

        $actionVerb = $isActive ? 'activated' : 'deactivated';
        session()->flash('success', count($ticketIdsToUpdate) . ' ticket(s) have been ' . $actionVerb . '.');

        $this->selectedCodes = []; // Clear selection after action
    }

    public function render()
    {
        return view('livewire.admin.order-details');
    }

    /**
     * Re-queue the ticket-image generation job for any ticket of this
     * order that is still missing both `image_path` and `qr_code_path`.
     *
     * Used as a manual recovery button on the order details page when the
     * original job failed midway (which leaves some tickets with paths and
     * others without). We only re-process tickets that need it so we don't
     * waste worker time on tickets that already have images.
     */
    public function regenerateMissingImages(): void
    {
        $missing = $this->order->tickets()
            ->where(function ($q) {
                $q->whereNull('image_path')
                  ->orWhere('image_path', '');
            })
            ->where(function ($q) {
                $q->whereNull('qr_code_path')
                  ->orWhere('qr_code_path', '');
            })
            ->pluck('id');

        if ($missing->isEmpty()) {
            session()->flash('info', __('order_details.actions.regenerate_no_missing'));
            return;
        }

        GenerateTicketImagesJob::dispatch($this->order->id);

        session()->flash('success', __('order_details.actions.regenerate_queued', [
            'count' => $missing->count(),
        ]));
    }
}
