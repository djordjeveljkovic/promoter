<x-layouts.app :title="__('sub_promoter_dashboard.page_title')">
    <div class="space-y-6">

        {{-- ===================== Flash messages ===================== --}}
        @if(session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- ===================== Page Header ===================== --}}
        <x-ui.page-header
            :eyebrow="__('sub_promoter_dashboard.eyebrow')"
            :title="__('sub_promoter_dashboard.main_heading')"
        >
            @if($manager)
                <p class="mt-2 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('sub_promoter_dashboard.managed_by_prefix') }}
                    <span class="font-semibold text-zinc-700 dark:text-zinc-200">{{ $manager->name }}</span>
                    ({{ $manager->email }})
                </p>
            @else
                <p class="mt-2 max-w-2xl text-sm text-amber-700 dark:text-amber-400">
                    {{ __('sub_promoter_dashboard.no_manager_notice') }}
                </p>
            @endif
            <x-slot:actions>
                <x-ui.button variant="secondary" :href="route('sub_promoter.orders.index')">
                    {{ __('sub_promoter_dashboard.recent_orders.view_all_button') }}
                </x-ui.button>
                <x-ui.button variant="primary" :href="route('sub_promoter.orders.create')" icon="plus">
                    {{ __('sub_promoter_dashboard.recent_orders.new_order_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- ===================== Hero: what I owe to my manager ===================== --}}
        <section>
            <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-700 p-6 text-white shadow-lg sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-violet-100">
                            {{ __('sub_promoter_dashboard.financials.amount_owed') }}
                        </p>
                        <div class="mt-4 flex flex-wrap items-baseline gap-3">
                            @if($amountOwedToManager > 0)
                                <span class="text-4xl font-bold tracking-tight sm:text-5xl">
                                    {{ number_format($amountOwedToManager, 2) }}
                                </span>
                                <span class="text-lg font-medium text-violet-100/90">RSD</span>
                            @elseif($amountOwedToManager < 0)
                                <span class="text-4xl font-bold tracking-tight text-emerald-200 sm:text-5xl">
                                    {{ number_format(abs($amountOwedToManager), 2) }} RSD
                                </span>
                                <x-ui.badge variant="success" size="sm">
                                    {{ __('sub_promoter_dashboard.financials.debt_overpaid_indicator') }}
                                </x-ui.badge>
                            @else
                                <span class="text-4xl font-bold tracking-tight text-emerald-200 sm:text-5xl">
                                    {{ __('sub_promoter_dashboard.financials.debt_payoff_indicator') }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm sm:max-w-md sm:grid-cols-3">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-violet-100/80">{{ __('sub_promoter_dashboard.financials.gross_sales') }}</p>
                            <p class="mt-1 font-semibold">{{ number_format($subGrossSalesAllTime, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-violet-100/80">{{ __('sub_promoter_dashboard.financials.commission_earned') }}</p>
                            <p class="mt-1 font-semibold">{{ number_format($subCommissionAllTime, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-violet-100/80">{{ __('sub_promoter_dashboard.financials.amount_paid') }}</p>
                            <p class="mt-1 font-semibold">{{ number_format($amountAlreadyPaid, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ===================== Performance ===================== --}}
        <section>
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('sub_promoter_dashboard.performance.heading') }}
            </h2>
            <x-ui.kpi-strip :cols="4">
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.performance.orders_all_time')"
                    :value="number_format($subOrdersAllTime)"
                />
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.performance.tickets_all_time')"
                    :value="number_format($subTicketsSoldAllTime)"
                />
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.performance.orders_last_30')"
                    :value="number_format($subOrdersLast30Days)"
                />
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.performance.tickets_last_30')"
                    :value="number_format($subTicketsSoldLast30Days)"
                />
            </x-ui.kpi-strip>
        </section>

        {{-- ===================== Top tickets + Status breakdown ===================== --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-2">
                <x-ui.card.header
                    :title="__('sub_promoter_dashboard.top_tickets.heading')"
                />
                @if($subTicketTypePerformance->isEmpty())
                    <x-ui.empty-state
                        icon="ticket"
                        :title="__('sub_promoter_dashboard.top_tickets.no_data')"
                    />
                @else
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.top_tickets.header_type') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('sub_promoter_dashboard.top_tickets.header_quantity') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('sub_promoter_dashboard.top_tickets.header_revenue') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach($subTicketTypePerformance as $type)
                                <x-ui.table-row>
                                    <x-ui.table-cell nowrap>{{ $type->name }}</x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>{{ number_format($type->total_quantity_sold) }}</x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        <span class="font-semibold">{{ number_format($type->total_revenue_generated, 2) }} RSD</span>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </x-ui.table-body>
                    </x-ui.table>
                @endif
            </x-ui.card>

            <x-ui.card>
                <x-ui.card.header
                    :title="__('sub_promoter_dashboard.status_breakdown.heading')"
                />
                @if($subOrderStatusCounts->isEmpty())
                    <x-ui.empty-state
                        icon="chart-bar"
                        :title="__('sub_promoter_dashboard.status_breakdown.empty')"
                    />
                @else
                    @php $statusTotal = (int) $subOrderStatusCounts->sum(); @endphp
                    <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($subOrderStatusCounts as $status => $count)
                            @php
                                $statusKey = $status ?? 'unknown';
                                $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                                if ($statusText === 'orders.statuses.' . $statusKey) {
                                    $statusText = \Illuminate\Support\Str::ucfirst($statusKey);
                                }
                                $pct = $statusTotal > 0 ? round(((int) $count) / $statusTotal * 100) : 0;
                            @endphp
                            <li class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                                <div class="flex min-w-0 items-center gap-2">
                                    <x-ui.status-pill :status="$statusKey" size="sm" />
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $statusText }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $count }}</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">({{ $pct }}%)</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </section>

        {{-- ===================== Commission split (set by manager) ===================== --}}
        @if(!empty($overrides))
            <section>
                <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('sub_promoter_dashboard.commission_split.heading') }}
                </h2>
                <x-ui.card>
                    <x-ui.card.header />
                    <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                        @foreach($overrides as $typeId => $ov)
                            @php
                                $type = \App\Models\TicketType::find($typeId);
                                $mode = is_array($ov) ? ($ov['type'] ?? 'percentage') : 'percentage';
                            @endphp
                            <li class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $type?->name ?? __('sub_promoter_dashboard.commission_split.unknown_type') }}
                                </span>
                                <span class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                    @if($mode === 'fixed')
                                        {{ number_format((float) ($ov['fixed_amount'] ?? 0), 2) }} RSD {{ __('sub_promoter_dashboard.commission_split.per_ticket_suffix') }}
                                    @else
                                        {{ number_format((float) ($ov['percentage'] ?? 0), 2) }}%
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </x-ui.card>
            </section>
        @endif

        {{-- ===================== Payment history ===================== --}}
        <section>
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('sub_promoter_dashboard.payment_history.heading') }}
            </h2>
            <x-ui.card>
                <x-ui.card.header />
                @if($recentPayments->isEmpty())
                    <x-ui.empty-state
                        icon="banknotes"
                        :title="__('sub_promoter_dashboard.payment_history.empty')"
                    />
                @else
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.payment_history.date') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.payment_history.direction') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('sub_promoter_dashboard.payment_history.amount') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.payment_history.note') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.payment_history.recorded_by') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach($recentPayments as $payment)
                                @php $isSent = $payment->payer_id === $sub->id; @endphp
                                <x-ui.table-row>
                                    <x-ui.table-cell nowrap>{{ $payment->paid_at->format('d M Y') }}</x-ui.table-cell>
                                    <x-ui.table-cell>
                                        @if($isSent)
                                            <x-ui.badge variant="danger" icon="arrow-up-right">
                                                {{ __('sub_promoter_dashboard.payment_history.direction_to') }} {{ $payment->receiver?->name ?? '—' }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="success" icon="arrow-down">
                                                {{ $payment->payer?->name ?? '—' }}
                                            </x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        <span @class([
                                            'font-semibold',
                                            'text-rose-600 dark:text-rose-400' => $isSent,
                                            'text-emerald-600 dark:text-emerald-400' => ! $isSent,
                                        ])>
                                            {{ $isSent ? '−' : '+' }} {{ number_format((float) $payment->amount, 2) }} RSD
                                        </span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ $payment->note ?? '—' }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <span class="text-zinc-500 dark:text-zinc-400">{{ $payment->recorder?->name ?? '—' }}</span>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </x-ui.table-body>
                    </x-ui.table>
                @endif
            </x-ui.card>
        </section>

        {{-- ===================== Recent orders ===================== --}}
        <section>
            <div class="mb-4 flex items-end justify-between">
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    {{ __('sub_promoter_dashboard.recent_orders.heading') }}
                </h2>
                <x-ui.button variant="secondary" size="sm" :href="route('sub_promoter.orders.index')">
                    {{ __('sub_promoter_dashboard.recent_orders.view_all_button') }}
                </x-ui.button>
            </div>

            @if($recentOrders->isEmpty())
                <x-ui.card>
                    <x-ui.empty-state
                        icon="ticket"
                        :title="__('sub_promoter_dashboard.recent_orders.empty_title')"
                        :description="__('sub_promoter_dashboard.recent_orders.empty')"
                    >
                        <x-slot:actions>
                            <x-ui.button variant="primary" :href="route('sub_promoter.orders.create')" icon="plus">
                                {{ __('sub_promoter_dashboard.recent_orders.new_order_button') }}
                            </x-ui.button>
                        </x-slot:actions>
                    </x-ui.empty-state>
                </x-ui.card>
            @else
                <x-ui.card :padding="false">
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.recent_orders.header_order') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('sub_promoter_dashboard.recent_orders.header_customer') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('sub_promoter_dashboard.recent_orders.header_total') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="center">{{ __('sub_promoter_dashboard.recent_orders.header_status') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach($recentOrders as $order)
                                <x-ui.table-row>
                                    <x-ui.table-cell nowrap>
                                        <a href="{{ route('promoter.orders.show', $order->id) }}" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                                            #{{ $order->order_number }}
                                        </a>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <span class="text-zinc-500 dark:text-zinc-300">{{ $order->email }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        <span class="font-semibold text-zinc-900 dark:text-white">{{ number_format($order->total, 2) }} RSD</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="center">
                                        <x-ui.status-pill :status="$order->job_status ?? 'unknown'" />
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </x-ui.table-body>
                    </x-ui.table>
                </x-ui.card>
            @endif
        </section>
    </div>
</x-layouts.app>
</content>
</invoke>