{{-- Admin order-details view, redesigned to share the visual language of
     /promoter/orders/{id} (resources/views/pages/promoters/orders/show.blade.php).
     The functional surface is unchanged: filter by type, edit paid amount,
     select & activate/deactivate tickets, download selected/all QR codes. --}}

<div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:px-8 sm:py-8 lg:px-8 lg:py-10">

        {{-- ===================== Page Header ===================== --}}
        <header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                    {{ __('order_details.header.eyebrow') }}
                </p>
                <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    {{ __('order_details.header.main_heading', ['id' => $order->id]) }}
                </h1>
                <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                    {{ __('order_details.header.sub_heading') }}
                </p>
            </div>
            <a href="{{ route('admin.orders.index') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                {{ __('order_details.header.back_to_orders') }}
            </a>
        </header>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-700 dark:text-green-100">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-700 dark:text-red-100">
                {{ session('error') }}
            </div>
        @endif
        @if (session('info'))
            <div class="mb-4 rounded-md bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-700 dark:text-blue-100">
                {{ session('info') }}
            </div>
        @endif

        {{-- ===================== Order Summary (KPI strip) ===================== --}}
        <section class="mb-8 grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('order_details.summary.customer_label') }}
                </span>
                <span class="text-base font-semibold text-gray-900 dark:text-white break-all">
                    {{ $order->email }}
                </span>
            </div>
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('order_details.summary.placed_on_label') }}
                </span>
                <span class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ $order->created_at->format('M d, Y H:i') }}
                </span>
            </div>
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('order_details.summary.total_label') }}
                </span>
                <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ number_format($totalPrice, 2) }} RSD
                </span>
                @if($showPaidInput)
                    <form wire:submit.prevent="updatePayment" class="mt-3 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <label for="paidAmount" class="sr-only sm:not-sr-only text-sm font-medium text-gray-700 dark:text-slate-300">
                            {{ __('order_details.payment.paid_amount_label') }}
                        </label>
                        <input type="text" id="paidAmount" wire:model.lazy="paid" onfocus="this.select()"
                               class="border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200 rounded-md px-3 py-1.5 text-sm w-full sm:w-32 focus:ring-yellow-500 focus:border-yellow-500" />
                        <button type="submit"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-1.5 rounded-md shadow-sm transition-colors">
                            {{ __('order_details.payment.update_button') }}
                        </button>
                        <button type="button" wire:click="togglePaidInput"
                                class="bg-gray-200 hover:bg-gray-300 dark:bg-slate-600 dark:hover:bg-slate-500 text-gray-700 dark:text-slate-300 text-sm px-3 py-1.5 rounded-md border border-gray-300 dark:border-slate-500 shadow-sm transition-colors">
                            {{ __('order_details.payment.cancel_button') }}
                        </button>
                    </form>
                    @error('paid')
                        <span class="text-red-500 dark:text-red-400 text-xs mt-1">{{ $message }}</span>
                    @enderror
                @else
                    <span class="mt-2 flex items-center gap-2">
                        <span class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('order_details.summary.paid_label') }}
                        </span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ number_format($this->order->paid, 2) }} RSD
                        </span>
                        <button wire:click="togglePaidInput"
                                class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 text-xs font-medium py-0.5 px-2 rounded hover:bg-indigo-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition-colors">
                            {{ __('order_details.payment.edit_paid_button') }}
                        </button>
                    </span>
                @endif
            </div>
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('order_details.summary.status_label') }}
                </span>
                <span>
                    @php
                        $statusKey = $order->job_status ?? 'unknown';
                        $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                        if ($statusText === 'orders.statuses.' . $statusKey) {
                            $statusText = \Illuminate\Support\Str::ucfirst($order->job_status ?? __('orders.statuses.unknown'));
                        }
                        $statusClass = [
                            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
                            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
                            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
                            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
                            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
                            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
                        ][$order->job_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
                    @endphp
                    <span class="inline-flex items-center px-2 text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </span>
            </div>
        </section>

        {{-- ===================== Filter by Ticket Type ===================== --}}
        @if($groupedTickets->isNotEmpty())
            <section class="mb-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <label for="ticketTypeFilter" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('order_details.filter.label') }}
                        </label>
                        <select wire:model.live="ticketTypeFilter" id="ticketTypeFilter"
                                class="mt-1 block w-full sm:w-64 rounded-lg border-gray-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="all">{{ __('order_details.filter.all_types_option') }}</option>
                            @foreach($groupedTickets as $typeName => $tickets)
                                <option value="{{ Str::slug($typeName) }}">{{ $typeName }}</option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $missingCount = $groupedTickets->flatten(1)->filter(function ($t) {
                            return empty($t->image_path) && empty($t->qr_code_path);
                        })->count();
                    @endphp
                    @if($missingCount > 0)
                        <button type="button" wire:click="regenerateMissingImages"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">
                            {{ __('order_details.actions.regenerate_button', ['count' => $missingCount]) }}
                        </button>
                    @endif
                </div>
            </section>
        @endif

        {{-- ===================== Tickets / QR Codes ===================== --}}
        <section class="mb-8">
            @php
                $itemsDisplayedInFilter = false;
            @endphp

            @if($groupedTickets->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-zinc-200">{{ __('order_details.tickets.none_found_header') }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">{{ __('order_details.tickets.none_found_message') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($groupedTickets as $typeName => $tickets)
                        @foreach($tickets as $ticket)
                            @php
                                $slug = Str::slug($typeName);
                                if ($ticketTypeFilter === 'all' || $ticketTypeFilter === $slug) {
                                    $itemsDisplayedInFilter = true;

                                    // Show qr_code_path (raw QR) if available,
                                    // otherwise fall back to image_path (the
                                    // composite ticket with the QR baked in).
                                    $imageRelPath = !empty($ticket->qr_code_path) ? $ticket->qr_code_path : $ticket->image_path;
                                    $hasImage = !empty($imageRelPath)
                                        && \Illuminate\Support\Facades\Storage::disk('public')->exists($imageRelPath);
                                    $isQrSource = !empty($ticket->qr_code_path) && $ticket->qr_code_path === $imageRelPath;
                                }
                            @endphp
                            @if($ticketTypeFilter === 'all' || $ticketTypeFilter === $slug)
                                <div class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800 transition hover:shadow-md">
                                    @if($hasImage)
                                        <a href="{{ asset('storage/' . $imageRelPath) }}" target="_blank" rel="noopener"
                                           class="flex items-center justify-center bg-white p-4 dark:bg-white"
                                           title="{{ __('order_details.tickets.image_alt_prefix') }} {{ $ticket->id }}">
                                            <img src="{{ asset('storage/' . $imageRelPath) }}"
                                                 alt="{{ __('order_details.tickets.image_alt_prefix') }} {{ $ticket->id }}"
                                                 class="h-40 w-40 object-contain">
                                        </a>
                                    @else
                                        <div class="flex h-44 flex-col items-center justify-center gap-1 bg-zinc-100 px-4 text-center dark:bg-zinc-800">
                                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                                {{ __('order_details.tickets.qr_not_available') }}
                                            </span>
                                            <span class="text-[10px] text-gray-400 dark:text-gray-500">
                                                {{ __('order_details.tickets.image_not_found') }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="flex flex-col gap-1 p-4">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ __('order_details.tickets.card_title_prefix') }}{{ $ticket->id }}
                                            </span>
                                            <label class="inline-flex items-center cursor-pointer gap-2 rounded-md transition-colors hover:bg-gray-100 dark:hover:bg-zinc-800 p-1 -m-1">
                                                <input type="checkbox"
                                                       wire:model.live="selectedCodes"
                                                       {{in_array($ticket->code, $selectedCodes) ? 'checked' : ''}}
                                                       value="{{ $ticket->code }}"
                                                       class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:ring-offset-zinc-900 focus:ring-2 dark:bg-zinc-700 dark:border-zinc-600" />
                                                <span class="text-xs text-gray-600 dark:text-gray-300">
                                                    @if(in_array($ticket->code, $selectedCodes))
                                                        {{ __('order_details.tickets.select_checkbox_checked') }}
                                                    @else
                                                        {{ __('order_details.tickets.select_checkbox_unchecked') }}
                                                    @endif
                                                </span>
                                            </label>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 break-all">
                                            {{ $ticket->ticketType->name ?? __('order_details.tickets.unknown_type') }}
                                        </span>
                                        <span class="mt-2">
                                            @if($ticket->is_active)
                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                    {{ __('order_details.tickets.status_active') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                                    {{ __('order_details.tickets.status_inactive') }}
                                                </span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach

                    @if(!$itemsDisplayedInFilter && $ticketTypeFilter !== 'all')
                        <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                            <p class="text-gray-600 dark:text-gray-400 text-sm">
                                {{ __('order_details.tickets.none_match_filter') }}
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        {{-- ===================== Bulk Actions ===================== --}}
        @if(!$groupedTickets->isEmpty() && $groupedTickets->flatten(1)->isNotEmpty())
            <section class="mb-8 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    {{ __('order_details.actions.group_title') }}
                </h3>
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                        @csrf
                        @foreach($selectedCodes as $code)
                            <input type="hidden" name="selected_codes[]" value="{{ $code }}">
                        @endforeach
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                @if(empty($selectedCodes)) disabled @endif>
                            {{ __('order_details.actions.download_selected_button') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-600 dark:bg-slate-600 dark:hover:bg-slate-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500">
                            {{ __('order_details.actions.download_all_button') }}
                        </button>
                    </form>

                    <button type="button" wire:click="updateSelectedTicketsActiveStatus(true)"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            @if(empty($selectedCodes)) disabled @endif>
                        {{ __('order_details.actions.activate_selected_button') }}
                    </button>
                    <button type="button" wire:click="updateSelectedTicketsActiveStatus(false)"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            @if(empty($selectedCodes)) disabled @endif>
                        {{ __('order_details.actions.deactivate_selected_button') }}
                    </button>
                </div>
            </section>
        @endif
    </div>
</div>