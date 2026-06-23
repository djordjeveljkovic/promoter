<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\EmailSettingsController;
use App\Http\Controllers\PromoterController;
use App\Http\Controllers\PromoterManagerController;
use App\Http\Controllers\SupremeAdminController;
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

        // Promoter-manager CRUD
        Route::get('/promoter-managers', [PromoterManagerController::class, 'index'])->name('admin.promoter_managers.index');
        Route::get('/promoter-manager/create', [PromoterManagerController::class, 'create'])->name('admin.promoter_managers.create');
        Route::post('/promoter-managers', [PromoterManagerController::class, 'store'])->name('admin.promoter_managers.store');
        Route::get('/promoter-manager/edit/{id}', [PromoterManagerController::class, 'edit'])->name('admin.promoter_managers.edit');
        Route::put('/promoter-manager/edit/{id}', [PromoterManagerController::class, 'update'])->name('admin.promoter_managers.update');
        Route::delete('/promoter-manager/{id}', [PromoterManagerController::class, 'destroy'])->name('admin.promoter_managers.destroy');

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

        // Email settings (admin only): see current config + manage email templates.
        Route::get('/email-settings', [EmailSettingsController::class, 'index'])
            ->name('admin.email-settings.index');
        Route::put('/email-settings/mail-config', [EmailSettingsController::class, 'updateMailConfig'])
            ->name('admin.email-settings.mail-config.update');
        Route::post('/email-settings/test-email', [EmailSettingsController::class, 'sendTestEmail'])
            ->name('admin.email-settings.test-email');
        Route::get('/email-settings/templates/create', [EmailSettingsController::class, 'createTemplate'])
            ->name('admin.email-settings.templates.create');
        Route::post('/email-settings/templates', [EmailSettingsController::class, 'storeTemplate'])
            ->name('admin.email-settings.templates.store');
        Route::get('/email-settings/templates/{emailTemplate}/edit', [EmailSettingsController::class, 'editTemplate'])
            ->name('admin.email-settings.templates.edit');
        Route::put('/email-settings/templates/{emailTemplate}', [EmailSettingsController::class, 'updateTemplate'])
            ->name('admin.email-settings.templates.update');
        Route::put('/email-settings/templates/{emailTemplate}/source', [EmailSettingsController::class, 'updateTemplateSource'])
            ->name('admin.email-settings.templates.source.update');
        Route::get('/email-settings/templates/{emailTemplate}/preview', [EmailSettingsController::class, 'previewTemplate'])
            ->name('admin.email-settings.templates.preview');
        Route::post('/email-settings/templates/{emailTemplate}/duplicate', [EmailSettingsController::class, 'duplicateTemplate'])
            ->name('admin.email-settings.templates.duplicate');
        Route::patch('/email-settings/templates/{emailTemplate}/activate', [EmailSettingsController::class, 'activateTemplate'])
            ->name('admin.email-settings.templates.activate');
        Route::delete('/email-settings/templates/{emailTemplate}', [EmailSettingsController::class, 'destroyTemplate'])
            ->name('admin.email-settings.templates.destroy');
    });
    Route::middleware('role:supreme|superadmin')->prefix('superadmin')->group(function () {
        // Bird's-eye overview of every promoter-manager and the sub-promoters
        // they created, with filters for commission earned, paid and owed.
        Route::get('/overview', [SupremeAdminController::class, 'overview'])
            ->name('supremeadmin.overview');
    });

    /**
     * Promoter Routes (regular promoter + promoter-manager + sub-promoter +
     * superadmin share these). The OrderController scopes every query to the
     * currently authenticated user, so a sub-promoter on this endpoint only
     * ever sees orders they placed themselves.
     *
     * A promoter-manager has the same commission logic as a promoter, but
     * additionally manages his own sub-promoters via the /promoter-manager
     * routes below.
     */
    Route::middleware('role:promoter|promoter_manager|sub_promoter|superadmin')->prefix('promoter')->group(function () {
        Route::get('/dashboard', [PromoterController::class, 'dashboard'])->name('promoter.dashboard');
        Route::get('/help', [PromoterController::class, 'help'])->name('promoter.help');
        Route::get('/orders', [OrderController::class, 'index'])->name('promoter.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('promoter.orders.show');
    });

    /**
     * Promoter-manager specific routes - sub-promoter management.
     */
    Route::middleware('role:promoter_manager|superadmin')->prefix('promoter-manager')->group(function () {
        Route::get('/dashboard', [PromoterManagerController::class, 'dashboard'])->name('promoter_manager.dashboard');

        Route::get('/sub-promoters', [PromoterManagerController::class, 'subPromotersIndex'])->name('promoter_manager.sub_promoters.index');
        Route::get('/sub-promoter/create', [PromoterManagerController::class, 'subPromoterCreate'])->name('promoter_manager.sub_promoters.create');
        Route::post('/sub-promoters', [PromoterManagerController::class, 'subPromoterStore'])->name('promoter_manager.sub_promoters.store');
        Route::get('/sub-promoter/edit/{id}', [PromoterManagerController::class, 'subPromoterEdit'])->name('promoter_manager.sub_promoters.edit');
        Route::put('/sub-promoter/edit/{id}', [PromoterManagerController::class, 'subPromoterUpdate'])->name('promoter_manager.sub_promoters.update');
        Route::delete('/sub-promoter/{id}', [PromoterManagerController::class, 'subPromoterDestroy'])->name('promoter_manager.sub_promoters.destroy');
    });

    /**
     * Sub-Promoter Routes
     */
    Route::middleware('role:sub_promoter')->prefix('sub-promoter')->group(function () {
        Route::get('/dashboard', [SubPromoterController::class, 'dashboard'])->name('sub_promoter.dashboard');
        Route::get('/orders', [SubPromoterController::class, 'ordersIndex'])->name('sub_promoter.orders.index');
        Route::get('/order/create', [SubPromoterController::class, 'create'])->name('sub_promoter.orders.create');
        Route::post('/orders', [SubPromoterController::class, 'placeOrder'])->name('sub_promoter.orders.store');
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
