{{-- Admin order-details view, redesigned to share the visual language of
     /promoter/orders/{id} (resources/views/pages/promoters/orders/show.blade.php).
     The functional surface is unchanged: filter by type, edit paid amount,
     select & activate/deactivate tickets, download selected/all QR codes. --}}

<div class="space-y-6">

    {{-- ===================== Page Header ===================== --}}
    <x-ui.page-header
        :eyebrow="__('order_details.header.eyebrow')"
        :title="__('order_details.header.main_heading', ['id' => $order->id])"
        :subtitle="__('order_details.header.sub_heading')"
    >
        <x-slot:actions>
            <x-ui.link variant="secondary" :href="route('admin.orders.index')">
                {{ __('order_details.header.back_to_orders') }}
            </x-ui.link>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Flash messages --}}
    @if (session('success'))
        <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
    @endif
    @if (session('info'))
        <x-ui.alert variant="info">{{ session('info') }}</x-ui.alert>
    @endif

    {{-- ===================== Order Summary (KPI strip) ===================== --}}
    <x-ui.kpi-strip :cols="4">
        {{-- Customer --}}
        <x-ui.stat-card
            :label="__('order_details.summary.customer_label')"
            :value="$order->email"
            icon="envelope"
            tone="indigo"
        />

        {{-- Date --}}
        <x-ui.stat-card
            :label="__('order_details.summary.placed_on_label')"
            :value="$order->created_at->format('M d, Y H:i')"
            icon="calendar"
            tone="neutral"
        />

        {{-- Total + Paid editor (custom cell — matches stat-card visual, but with an interactive form) --}}
        <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('order_details.summary.total_label') }}
                </p>
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                    <x-ui.icon name="banknotes" class="h-5 w-5" />
                </span>
            </div>
            <span class="text-lg font-bold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-xl">
                {{ number_format($totalPrice, 2) }} RSD
            </span>

            @if($showPaidInput)
                <form wire:submit.prevent="updatePayment" class="mt-2 flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2">
                    <label for="paidAmount" class="sr-only sm:not-sr-only text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('order_details.payment.paid_amount_label') }}
                    </label>
                    <x-ui.input id="paidAmount" type="text" wire:model.lazy="paid" size="sm" class="w-full sm:w-32" onfocus="this.select()" />
                    <x-ui.button type="submit" variant="warning" size="sm">{{ __('order_details.payment.update_button') }}</x-ui.button>
                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="togglePaidInput">{{ __('order_details.payment.cancel_button') }}</x-ui.button>
                </form>
                @error('paid')
                    <span class="text-xs text-rose-600 dark:text-rose-400" role="alert">{{ $message }}</span>
                @enderror
            @else
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="text-xs uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('order_details.summary.paid_label') }}
                    </span>
                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">
                        {{ number_format($order->paid, 2) }} RSD
                    </span>
                    <x-ui.button type="button" variant="link" size="sm" wire:click="togglePaidInput" icon="pencil-square">
                        {{ __('order_details.payment.edit_paid_button') }}
                    </x-ui.button>
                </div>
            @endif
        </div>

        {{-- Status --}}
        <x-ui.stat-card
            :label="__('order_details.summary.status_label')"
            icon="ticket"
            tone="indigo"
        >
            @php
                $jobStatusKey = $order->job_status ?? 'unknown';
                $jobStatusText = __('admin_orders.statuses.' . $jobStatusKey, [], app()->getLocale());
                if ($jobStatusText === 'admin_orders.statuses.' . $jobStatusKey) {
                    $jobStatusText = \Illuminate\Support\Str::ucfirst($jobStatusKey === 'unknown' ? __('admin_orders.statuses.unknown') : $jobStatusKey);
                }
            @endphp
            <x-ui.status-pill :status="$jobStatusKey" :label="$jobStatusText" />
        </x-ui.stat-card>
    </x-ui.kpi-strip>

    {{-- ===================== Filter by Ticket Type ===================== --}}
    @if($groupedTickets->isNotEmpty())
        <x-ui.card>
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-end sm:justify-between sm:p-6">
                <x-ui.field
                    :label="__('order_details.filter.label')"
                    for="ticketTypeFilter"
                >
                    <x-ui.select
                        id="ticketTypeFilter"
                        wire:model.live="ticketTypeFilter"
                        class="sm:w-64"
                    >
                        <option value="all">{{ __('order_details.filter.all_types_option') }}</option>
                        @foreach($groupedTickets as $typeName => $tickets)
                            <option value="{{ Str::slug($typeName) }}">{{ $typeName }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>

                @php
                    $missingCount = $groupedTickets->flatten(1)->filter(function ($t) {
                        return empty($t->image_path) && empty($t->qr_code_path);
                    })->count();
                @endphp
                @if($missingCount > 0)
                    <x-ui.button type="button" variant="warning" wire:click="regenerateMissingImages">
                        {{ __('order_details.actions.regenerate_button', ['count' => $missingCount]) }}
                    </x-ui.button>
                @endif
            </div>
        </x-ui.card>
    @endif

    {{-- ===================== Tickets / QR Codes ===================== --}}
    @if($groupedTickets->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                icon="ticket"
                :title="__('order_details.tickets.none_found_header')"
                :description="__('order_details.tickets.none_found_message')"
            />
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($groupedTickets as $typeName => $tickets)
                @foreach($tickets as $ticket)
                    @php
                        $slug = Str::slug($typeName);
                    @endphp
                    @if($ticketTypeFilter === 'all' || $ticketTypeFilter === $slug)
                        @php
                            // Show qr_code_path (raw QR) if available,
                            // otherwise fall back to image_path (the
                            // composite ticket with the QR baked in).
                            $imageRelPath = !empty($ticket->qr_code_path) ? $ticket->qr_code_path : $ticket->image_path;
                            $hasImage = !empty($imageRelPath)
                                && \Illuminate\Support\Facades\Storage::disk('public')->exists($imageRelPath);
                        @endphp
                        <x-ui.card padding="false" class="overflow-hidden transition hover:shadow-md">
                            @if($hasImage)
                                <a href="{{ asset('storage/' . $imageRelPath) }}" target="_blank" rel="noopener"
                                   class="flex items-center justify-center bg-white p-4"
                                   title="{{ __('order_details.tickets.image_alt_prefix') }} {{ $ticket->id }}">
                                    <img src="{{ asset('storage/' . $imageRelPath) }}"
                                         alt="{{ __('order_details.tickets.image_alt_prefix') }} {{ $ticket->id }}"
                                         class="h-40 w-40 object-contain">
                                </a>
                            @else
                                <div class="flex h-44 flex-col items-center justify-center gap-1 bg-zinc-100 px-4 text-center dark:bg-zinc-800">
                                    <span class="whitespace-pre-line text-xs font-medium text-zinc-600 dark:text-zinc-300">
                                        {{ __('order_details.tickets.qr_not_available') }}
                                    </span>
                                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500">
                                        {{ __('order_details.tickets.image_not_found') }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex flex-col gap-2 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ __('order_details.tickets.card_title_prefix') }}{{ $ticket->id }}
                                    </span>
                                    <x-ui.checkbox
                                        wire:model.live="selectedCodes"
                                        value="{{ $ticket->code }}"
                                        :label="in_array($ticket->code, $selectedCodes)
                                            ? __('order_details.tickets.select_checkbox_checked')
                                            : __('order_details.tickets.select_checkbox_unchecked')"
                                    />
                                </div>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 break-all">
                                    {{ $ticket->ticketType->name ?? __('order_details.tickets.unknown_type') }}
                                </span>
                                @if($ticket->is_active)
                                    <x-ui.badge variant="success" size="sm">
                                        {{ __('order_details.tickets.status_active') }}
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger" size="sm">
                                        {{ __('order_details.tickets.status_inactive') }}
                                    </x-ui.badge>
                                @endif
                            </div>
                        </x-ui.card>
                    @endif
                @endforeach
            @endforeach

            @if($ticketTypeFilter !== 'all')
                <div class="col-span-full rounded-xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('order_details.tickets.none_match_filter') }}
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- ===================== Bulk Actions ===================== --}}
    @if(!$groupedTickets->isEmpty() && $groupedTickets->flatten(1)->isNotEmpty())
        <x-ui.card>
            <div class="p-5 sm:p-6">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('order_details.actions.group_title') }}
                </h3>
                <div class="flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                        @csrf
                        @foreach($selectedCodes as $code)
                            <input type="hidden" name="selected_codes[]" value="{{ $code }}">
                        @endforeach
                        <x-ui.button type="submit" variant="primary" :disabled="empty($selectedCodes)">
                            {{ __('order_details.actions.download_selected_button') }}
                        </x-ui.button>
                    </form>

                    <form method="POST" action="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">
                            {{ __('order_details.actions.download_all_button') }}
                        </x-ui.button>
                    </form>

                    <x-ui.button
                        type="button"
                        variant="success"
                        wire:click="updateSelectedTicketsActiveStatus(true)"
                        :disabled="empty($selectedCodes)"
                    >
                        {{ __('order_details.actions.activate_selected_button') }}
                    </x-ui.button>

                    <x-ui.button
                        type="button"
                        variant="danger"
                        wire:click="updateSelectedTicketsActiveStatus(false)"
                        :disabled="empty($selectedCodes)"
                    >
                        {{ __('order_details.actions.deactivate_selected_button') }}
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    @endif
</div>