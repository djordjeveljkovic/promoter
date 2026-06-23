<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\PromoterController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SubPromoterController;
use App\Livewire\Admin\OrderDetails;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function() { return view('welcome'); });
Route::get('/karte', function () {
    $tickets = \App\Models\Ticket::with('ticketType')->get();

    $grouped = $tickets->groupBy(function ($ticket) {
        return strtolower($ticket->ticketType->name ?? 'unknown');
    })->map(function ($group) {
        return $group->pluck('code')->all();
    });

    return response()->json($grouped);
});
// Protected routes
Route::middleware('auth')->group(function () {

    /**
     * Admin Routes
     */
    Route::middleware('role:admin|superadmin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/promoters', [AdminController::class, 'promoters'])->name('admin.promoters.index');
        Route::get('/promoter/create', [AdminController::class, 'createPromoter'])->name('admin.promoters.create');
        Route::get('/promoter/edit/{id}', [AdminController::class, 'editPromoter'])->name('admin.promoters.edit');

        Route::post('/promoters', [AdminController::class, 'store'])->name('admin.promoters.store');
        Route::put('/promoter/edit/{id}', [AdminController::class, 'updatePromoter'])->name('admin.promoters.update');

        Route::delete('/promoter/{id}', [AdminController::class, 'deletePromoter'])->name('admin.promoters.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
        /* Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.orders.show'); */

        Route::get('/orders/{id}', OrderDetails::class)->name('admin.orders.show');
        Route::post('/orders/{order}/download-qrcodes', [AdminOrderController::class, 'downloadQRCodes'])->name('admin.orders.downloadQRCodes');
        Route::put('/orders/{order}/update-payment', [AdminOrderController::class, 'updatePayment'])->name('admin.orders.updatePayment');

        Route::get('/order/create', [AdminOrderController::class, 'create'])->name('admin.orders.create');
        Route::post('/orders', [AdminOrderController::class, 'store'])->name('admin.orders.store');

        Route::get('/tickets', [TicketController::class, 'index'])->name('ticket_type.index');
        Route::get('/ticket/create', [TicketController::class, 'create'])->name('ticket_type.create');
        Route::get('/ticket/edit/{id}', [TicketController::class, 'edit'])->name('ticket_type.edit');
        Route::delete('/ticket/{id}/destroy', [TicketController::class, 'destroy'])->name('ticket_type.destroy');
        Route::post('/ticket/store', [TicketController::class, 'store'])->name('ticket_type.store');
        Route::put('/ticket/update/{id}', [TicketController::class, 'update'])->name('ticket_type.update');
        Route::put('/ticket-types/{id}/photo', [TicketController::class, 'uploadPhoto']);
        Route::put('/ticket-types/{id}/qr', [TicketController::class, 'setQrCoordinates']);
        Route::put('/ticket-types/{id}/price', [TicketController::class, 'setPrice']);
        Route::put('/commissions', [AdminController::class, 'setCommission']);
    });
    Route::middleware('role:superadmin')->prefix('superadmin')->group(function () {
    });

    /**
     * Promoter Routes
     */
    Route::middleware('role:promoter|superadmin')->prefix('promoter')->group(function () {
        Route::get('/dashboard', [PromoterController::class, 'dashboard'])->name('promoter.dashboard');
        Route::get('/help', [PromoterController::class, 'help'])->name('promoter.help');
        Route::get('/orders', [OrderController::class, 'index'])->name('promoter.orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('promoter.orders.show');

        Route::post('/sub-promoters', [PromoterController::class, 'createSubPromoter']);
    });

    /**
     * Sub-Promoter Routes
     */
    Route::middleware('role:sub_promoter')->prefix('sub-promoter')->group(function () {
        Route::get('/dashboard', [SubPromoterController::class, 'dashboard']);
        Route::post('/orders', [SubPromoterController::class, 'placeOrder']);
    });
});
Route::middleware(['auth'])->group(function () {
    Route::get('/orders/{id}/show', [OrderController::class, 'showPublic'])->name('orders.show');
    Route::get('/order/create', [OrderController::class, 'create'])->name('promoter.orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('promoter.orders.store');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::post('/orders/{order}/rerun-image-job', [OrderController::class, 'rerunImageGeneration'])->name('orders.rerunImageJob');
    Route::post('/orders/{order}/rerun-email-job', [OrderController::class, 'rerunEmailSending'])->name('orders.rerunEmailJob');
});

require __DIR__ . '/auth.php';
