<x-layouts.app :title="__('sub_promoter_dashboard.page_title')">
    @php
        use App\Support\Status;
    @endphp

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
                        <p class="mt-1 text-sm text-violet-100/90 max-w-2xl">
                            {{ __('sub_promoter_dashboard.financials.debt_formula') }}
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

        {{-- ===================== Pyramid / How the money flows ===================== --}}
        <x-ui.card>
            <x-ui.card.header
                :title="__('sub_promoter_dashboard.pyramid.heading')"
                :subtitle="__('sub_promoter_dashboard.pyramid.help')"
            />
            <div class="p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                    {{-- Gross --}}
                    <div class="rounded-lg border border-sky-200 bg-sky-50 p-4 dark:border-sky-700/40 dark:bg-sky-900/20">
                        <p class="text-xs font-medium uppercase tracking-wider text-sky-700 dark:text-sky-300">
                            {{ __('sub_promoter_dashboard.pyramid.row_gross') }}
                        </p>
                        <p class="mt-1 text-xl font-bold text-sky-900 dark:text-sky-100">
                            {{ number_format($subGrossSalesAllTime, 2) }} <span class="text-xs font-medium text-sky-700 dark:text-sky-300">RSD</span>
                        </p>
                    </div>
                    <div class="hidden items-center justify-center text-zinc-300 dark:text-zinc-600 md:flex">
                        <x-ui.icon name="minus" class="size-6" />
                    </div>
                    {{-- Sub commission --}}
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-700/40 dark:bg-emerald-900/20">
                        <p class="text-xs font-medium uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                            {{ __('sub_promoter_dashboard.pyramid.row_sub_commission') }}
                        </p>
                        <p class="mt-1 text-xl font-bold text-emerald-900 dark:text-emerald-100">
                            {{ number_format($subCommissionAllTime, 2) }} <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">RSD</span>
                        </p>
                    </div>
                    <div class="hidden items-center justify-center text-zinc-300 dark:text-zinc-600 md:flex">
                        <x-ui.icon name="minus" class="size-6" />
                    </div>
                    {{-- Remaining --}}
                    <div @class([
                        'rounded-lg p-4 border-2',
                        'border-rose-300 bg-rose-50 dark:border-rose-700/40 dark:bg-rose-900/20' => $amountOwedToManager > 0,
                        'border-emerald-300 bg-emerald-50 dark:border-emerald-700/40 dark:bg-emerald-900/20' => $amountOwedToManager <= 0,
                    ])>
                        <p @class([
                            'text-xs font-medium uppercase tracking-wider',
                            'text-rose-700 dark:text-rose-300' => $amountOwedToManager > 0,
                            'text-emerald-700 dark:text-emerald-300' => $amountOwedToManager <= 0,
                        ])>
                            {{ __('sub_promoter_dashboard.pyramid.row_amount_due') }}
                        </p>
                        <p @class([
                            'mt-1 text-xl font-bold',
                            'text-rose-900 dark:text-rose-100' => $amountOwedToManager > 0,
                            'text-emerald-900 dark:text-emerald-100' => $amountOwedToManager <= 0,
                        ])>
                            {{ number_format(max($amountOwedToManager, 0), 2) }} <span @class([
                                'text-xs font-medium',
                                'text-rose-700 dark:text-rose-300' => $amountOwedToManager > 0,
                                'text-emerald-700 dark:text-emerald-300' => $amountOwedToManager <= 0,
                            ])>RSD</span>
                        </p>
                    </div>
                </div>
                <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('sub_promoter_dashboard.pyramid.row_already_paid') }}
                        </p>
                        <p class="mt-1 text-base font-semibold text-zinc-900 dark:text-white">
                            {{ number_format($amountAlreadyPaid, 2) }} RSD
                        </p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('sub_promoter_dashboard.pyramid.row_remaining') }}
                        </p>
                        <p @class([
                            'mt-1 text-base font-semibold',
                            'text-rose-600 dark:text-rose-400' => $amountOwedToManager > 0,
                            'text-emerald-600 dark:text-emerald-400' => $amountOwedToManager <= 0,
                        ])>
                            {{ number_format(max($amountOwedToManager, 0), 2) }} RSD
                        </p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('sub_promoter_dashboard.financials.commission_earned') }}
                        </p>
                        <p class="mt-1 text-base font-semibold text-emerald-600 dark:text-emerald-400">
                            {{ number_format($subCommissionAllTime, 2) }} RSD
                        </p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- ===================== Notice: payments are recorded by manager ===================== --}}
        <x-ui.card>
            <x-ui.card.header
                :title="__('sub_promoter_dashboard.record_payment_notice.heading')"
                :subtitle="__('sub_promoter_dashboard.record_payment_notice.helper_text')"
            />
            <div class="flex items-start gap-3 p-5 text-sm text-zinc-700 dark:text-zinc-200 sm:p-6">
                <x-ui.icon name="cog" class="mt-0.5 size-5 shrink-0 text-indigo-500" />
                <p>
                    {{ __('sub_promoter_dashboard.record_payment_notice.body') }}
                    @if($manager)
                        <span class="font-medium text-zinc-900 dark:text-white">{{ $manager->name }}</span>.
                    @endif
                </p>
            </div>
        </x-ui.card>

        {{-- ===================== Financials KPI cards ===================== --}}
        <section>
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('sub_promoter_dashboard.financials.heading') }}
            </h2>
            <x-ui.kpi-strip :cols="4">
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.financials.commission_earned')"
                    icon="banknotes"
                    tone="success"
                    :value="number_format($subCommissionAllTime, 2)"
                    :subtext="__('sub_promoter_dashboard.financials.all_time_label')"
                ><span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">RSD</span></x-ui.stat-card>
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.financials.gross_sales')"
                    icon="chart-bar"
                    tone="info"
                    :value="number_format($subGrossSalesAllTime, 2)"
                    :subtext="__('sub_promoter_dashboard.financials.gross_sales_subtext')"
                ><span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">RSD</span></x-ui.stat-card>
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.financials.amount_paid')"
                    icon="currency-dollar"
                    tone="success"
                    :value="number_format($amountAlreadyPaid, 2)"
                    :subtext="__('sub_promoter_dashboard.financials.amount_owed_subtext')"
                ><span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">RSD</span></x-ui.stat-card>
                <x-ui.stat-card
                    :label="__('sub_promoter_dashboard.financials.commission_last_30')"
                    icon="arrow-trending-up"
                    tone="indigo"
                    :value="number_format($subCommissionLast30Days, 2)"
                    :subtext="__('sub_promoter_dashboard.financials.commission_last_30_subtext')"
                ><span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">RSD</span></x-ui.stat-card>
            </x-ui.kpi-strip>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.card>
                    <div class="flex items-center justify-between gap-4 p-4">
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('sub_promoter_dashboard.financials.gross_sales_last_30') }}
                            </span>
                            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ number_format($subGrossSalesLast30Days, 2) }} RSD
                            </p>
                        </div>
                        <x-ui.icon name="calendar" class="size-5 text-zinc-400 dark:text-zinc-500" />
                    </div>
                </x-ui.card>
                <x-ui.card>
                    <div class="flex items-center justify-between gap-4 p-4">
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                {{ __('sub_promoter_dashboard.performance.orders_all_time') }}
                            </span>
                            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                                {{ number_format($subOrdersAllTime) }}
                            </p>
                        </div>
                        <x-ui.icon name="ticket" class="size-5 text-zinc-400 dark:text-zinc-500" />
                    </div>
                </x-ui.card>
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
                    :subtitle="__('sub_promoter_dashboard.top_tickets.help')"
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
                    :subtitle="__('sub_promoter_dashboard.status_breakdown.help')"
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
                    <x-ui.card.header :subtitle="__('sub_promoter_dashboard.commission_split.help')" />
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
                <x-ui.card.header :subtitle="__('sub_promoter_dashboard.payment_history.sub_heading')" />
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