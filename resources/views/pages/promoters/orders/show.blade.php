<x-layouts.app :title="__('orders.show_page_title', ['orderNumber' => $order->order_number])">
    <div class="space-y-6">

        {{-- ===================== Page Header ===================== --}}
        <x-ui.page-header
            :eyebrow="__('orders.show.eyebrow')"
            :title="__('orders.show.main_heading', ['orderNumber' => $order->order_number])"
            :subtitle="__('orders.show.sub_heading')"
        >
            <x-slot:actions>
                <x-ui.button variant="secondary" :href="route('promoter.orders.index')">
                    {{ __('orders.show.back_to_orders') }}
                </x-ui.button>
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

        {{-- ===================== Order Summary ===================== --}}
        <x-ui.kpi-strip :cols="4">
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('orders.show.summary.customer_label') }}
                </span>
                <span class="text-base font-semibold text-zinc-900 dark:text-white break-all">
                    {{ $order->email }}
                </span>
            </div>
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('orders.show.summary.placed_on_label') }}
                </span>
                <span class="text-base font-semibold text-zinc-900 dark:text-white">
                    {{ $order->created_at->format('M d, Y H:i') }}
                </span>
            </div>
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('orders.show.summary.total_label') }}
                </span>
                <span class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    {{ number_format($totalPrice, 2) }} RSD
                </span>
            </div>
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('orders.show.summary.status_label') }}
                </span>
                <div>
                    <x-ui.status-pill :status="$order->job_status ?? 'unknown'" />
                </div>
            </div>
        </x-ui.kpi-strip>

        {{-- ===================== Seller / Commission Breakdown ===================== --}}
        <x-ui.kpi-strip :cols="2">
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('orders.show.summary.seller_label') }}
                </span>
                <span class="text-base font-semibold text-zinc-900 dark:text-white">
                    @if($order->requested_by === Auth::id())
                        <x-ui.badge variant="indigo" size="sm">
                            {{ __('orders.seller_self_badge') }}
                        </x-ui.badge>
                        &middot; {{ $order->requestedBy->name ?? __('orders.seller_unknown') }}
                    @else
                        {{ $order->requestedBy->name ?? __('orders.seller_unknown') }}
                    @endif
                </span>
            </div>
            @php $myCommission = $commissionByOrder[$order->id] ?? 0.0; @endphp
            <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                    {{ __('orders.show.summary.my_commission_label') }}
                </span>
                <span class="text-2xl font-bold tracking-tight text-emerald-700 dark:text-emerald-400">
                    {{ number_format($myCommission, 2) }} RSD
                </span>
                @if($order->requested_by !== Auth::id() && $order->total_commission_earned > $myCommission)
                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('orders.show.summary.commission_split_note', ['total' => number_format($order->total_commission_earned, 2)]) }}
                    </span>
                @endif
            </div>
        </x-ui.kpi-strip>

        {{-- ===================== Items ===================== --}}
        <x-ui.card>
            <x-ui.card.header :title="__('orders.show.items.heading')" />
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('orders.show.items.header_type') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('orders.show.items.header_quantity') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('orders.show.items.header_unit_price') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('orders.show.items.header_subtotal') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @foreach($order->items as $item)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <span class="font-medium text-zinc-900 dark:text-white">
                                    {{ $item->ticketType->name ?? __('orders.show.items.unknown_type') }}
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>{{ $item->quantity }}</x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>{{ number_format($item->ticketType->price ?? 0, 2) }} RSD</x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>
                                <span class="font-semibold">{{ number_format($item->quantity * ($item->ticketType->price ?? 0), 2) }} RSD</span>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforeach
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>

        {{-- ===================== Tickets / QR Codes ===================== --}}
        <section>
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ __('orders.show.tickets.heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('orders.show.tickets.sub_heading', ['count' => $order->tickets->count()]) }}
                    </p>
                </div>
                @if($order->tickets->isNotEmpty())
                    <form method="POST" action="{{ route('promoter.orders.downloadQRCodes', ['order' => $order->id]) }}" class="inline">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">
                            {{ __('orders.show.tickets.download_all_button') }}
                        </x-ui.button>
                    </form>
                @endif
            </div>

            @if($order->tickets->isEmpty())
                <x-ui.card>
                    <x-ui.empty-state
                        icon="ticket"
                        :title="__('orders.show.tickets.empty')"
                    />
                </x-ui.card>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($order->tickets as $ticket)
                        @php
                            $hasQr = !empty($ticket->qr_code_path)
                                && \Illuminate\Support\Facades\Storage::disk('public')->exists($ticket->qr_code_path);
                        @endphp
                        <x-ui.card class="!overflow-hidden">
                            @if($hasQr)
                                <div class="flex items-center justify-center bg-white p-4 dark:bg-white">
                                    <img src="{{ asset('storage/' . $ticket->qr_code_path) }}"
                                         alt="{{ __('orders.show.tickets.image_alt_prefix') }} {{ $ticket->id }}"
                                         class="h-40 w-40 object-contain">
                                </div>
                            @else
                                <div class="flex h-44 items-center justify-center bg-zinc-100 px-4 text-center dark:bg-zinc-800">
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('orders.show.tickets.qr_not_available') }}
                                    </span>
                                </div>
                            @endif
                            <div class="flex flex-col gap-1 p-4">
                                <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ __('orders.show.tickets.card_title_prefix') }}{{ $ticket->id }}
                                </span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400 break-all">
                                    {{ $ticket->ticketType->name ?? __('orders.show.tickets.unknown_type') }}
                                </span>
                                <span class="mt-2">
                                    @if($ticket->is_active)
                                        <x-ui.badge variant="success" size="sm">
                                            {{ __('orders.show.tickets.status_active') }}
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge variant="danger" size="sm">
                                            {{ __('orders.show.tickets.status_inactive') }}
                                        </x-ui.badge>
                                    @endif
                                </span>
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
</content>
</invoke>