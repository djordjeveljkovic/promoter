<x-layouts.app :title="__('promoter_managers.dashboard.page_title')">
    @php
        // Tiny formatting helpers used throughout the dashboard.
        $fmt       = fn (float $v) => number_format($v, 2);
        $fmtInt    = fn (int $v)   => number_format($v, 0, '', '.');
        $fmtSigned = fn (float $v) => ($v >= 0 ? '+' : '−').number_format(abs($v), 2);

        // --- KPI inputs (Part 1 of overview) -------------------------------
        $myEarnings    = (float) $earningsBreakdown['total_commission'];
        $cashInHand    = (float) $cashInHand;
        $moneyWithSubs = (float) $teamOwedToManager;
        $oweAmount     = (float) $debtSummary['amount_owed_to_organizers'];

        // --- Personal / sub earnings inputs (Part 2 of overview) -----------
        $personalCommission   = (float) $earningsBreakdown['personal_commission'];
        $subCommissionShare   = (float) $earningsBreakdown['sub_commission'];
        $personalOrdersCount  = (int)    $personal['orders_count'];
        $personalTicketsCount = (int)    $personal['tickets_sold'];

        // Aggregate order count + tickets sold by the team (for Part 2).
        $subsOrdersCount  = (int) array_sum(array_column($subTypeBreakdowns, 'orders_count'));
        $subsTicketsCount = (int) array_sum(array_column($subTypeBreakdowns, 'tickets_sold'));

        // --- Per-sub rows (used by both leaderboard & subs_list section) ---
        $subsCombined = collect($subTypeBreakdowns)->map(function ($row) use ($topSubs, $subDebts) {
            $subId = $row['user']->id;
            $top  = $topSubs->firstWhere('user.id', $subId);
            $dbt  = $subDebts->firstWhere('user.id', $subId);
            return [
                'user'               => $row['user'],
                'tickets_sold'       => (int)   ($row['tickets_sold']     ?? 0),
                'orders_count'       => (int)   ($row['orders_count']     ?? 0),
                'gross_sales'        => (float) ($dbt['gross_sales']     ?? $row['total_gross']),
                'sub_commission'     => (float) ($dbt['sub_commission']  ?? 0),
                'manager_commission' => (float) ($top['manager_commission'] ?? 0),
                'paid'               => (float) ($dbt['amount_already_paid'] ?? 0),
                'owed'               => (float) ($dbt['amount_owed_to_manager'] ?? 0),
                'per_type'           => $row['rows'],
            ];
        })
        ->sortByDesc('tickets_sold')
        ->values();
    @endphp

    <div class="space-y-10">

        {{-- ===================== Flash messages ===================== --}}
        @if(session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- ===================== Header ===================== --}}
        <x-ui.page-header
            :eyebrow="__('promoter_managers.dashboard.eyebrow')"
            :title="__('promoter_managers.dashboard.main_heading')"
        >
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('promoter_manager.sub_promoters.index')" icon="users">
                    {{ __('promoter_managers.dashboard.manage_subs_button') }}
                </x-ui.button>
                <x-ui.button variant="secondary" :href="route('promoter.orders.create')" icon="ticket">
                    {{ __('promoter_managers.dashboard.new_order_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- ===================== Section 1 · OVERVIEW ===================== --}}
        <section aria-label="{{ __('promoter_managers.dashboard.overview.heading_part1') }}">

            {{-- Part 1 · 4 KPI kartice --------------------------------- --}}
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                    {{ __('promoter_managers.dashboard.overview.heading_part1') }}
                </h2>
                <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800 ml-3"></span>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- 1) Novac kod mene --}}
                <div class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 p-5 text-white shadow-lg sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                            {{ __('promoter_managers.dashboard.kpi.cash_in_hand.label') }}
                        </p>
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                            <x-ui.icon name="shopping-bag" class="size-4" />
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">
                            @if($cashInHand < 0)−@endif{{ $fmt(abs($cashInHand)) }}
                        </span>
                        <span class="text-sm font-medium text-white/80">RSD</span>
                    </div>
                    @if($cashInHand < 0)
                        <p class="mt-auto text-xs font-medium text-white/95">
                            {{ __('promoter_managers.dashboard.kpi.cash_in_hand.overpaid') }}
                        </p>
                    @endif
                </div>

                {{-- 2) Moja zarada --}}
                <div class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 p-5 text-white shadow-lg sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                            {{ __('promoter_managers.dashboard.kpi.my_earnings.label') }}
                        </p>
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                            <x-ui.icon name="banknotes" class="size-4" />
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">{{ $fmt($myEarnings) }}</span>
                        <span class="text-sm font-medium text-white/80">RSD</span>
                    </div>
                </div>

                {{-- 3) Novac kod promotera --}}
                <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                   class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 p-5 text-white shadow-lg transition hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                            {{ __('promoter_managers.dashboard.kpi.money_with_promoters.label') }}
                        </p>
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                            <x-ui.icon name="users" class="size-4" />
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">{{ $fmt($moneyWithSubs) }}</span>
                        <span class="text-sm font-medium text-white/80">RSD</span>
                    </div>
                    <span class="mt-auto inline-flex items-center gap-1 text-xs font-medium text-white/95">
                        {{ __('promoter_managers.dashboard.kpi.money_with_promoters.open_link') }}
                        <x-ui.icon name="arrow-up-right" class="size-3 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                    </span>
                </a>

                {{-- 4) Dug prema org --}}
                <div @class([
                    'group relative flex flex-col gap-3 overflow-hidden rounded-2xl p-5 text-white shadow-lg sm:p-6',
                    'bg-gradient-to-br from-rose-500 via-rose-600 to-orange-600' => $oweAmount > 0,
                    'bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600' => $oweAmount < 0,
                    'bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600' => $oweAmount == 0,
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                            {{ __('promoter_managers.dashboard.kpi.debt_to_organizers.label') }}
                        </p>
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                            <x-ui.icon name="currency-dollar" class="size-4" />
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        @if($oweAmount == 0)
                            <span class="text-2xl font-bold tracking-tight sm:text-3xl">
                                {{ __('promoter_managers.dashboard.kpi.debt_to_organizers.zero_label') }}
                            </span>
                        @else
                            <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">
                                @if($oweAmount < 0)−@endif{{ $fmt(abs($oweAmount)) }}
                            </span>
                            <span class="text-sm font-medium text-white/80">RSD</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Part 2 · Licna prodaja | Prodaja promotera ------------ --}}
            <div class="mt-8">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                        {{ __('promoter_managers.dashboard.overview.heading_part2') }}
                    </h2>
                    <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-800 ml-3"></span>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                    {{-- Licna prodaja --}}
                    <x-ui.card class="!rounded-2xl">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    <x-ui.icon name="user" class="size-4" />
                                </span>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ __('promoter_managers.dashboard.overview.my_sales') }}
                                </p>
                            </div>
                            <span class="text-2xl font-bold tabular-nums text-zinc-900 dark:text-white">
                                {{ $fmt((float) $personalTypeBreakdown['total_gross']) }}
                                <span class="text-xs font-medium text-zinc-500">RSD</span>
                            </span>
                        </div>

                        <dl class="grid grid-cols-3 divide-x divide-zinc-200 border-b border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                            <div class="px-4 py-3 text-center sm:px-6">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.my_sales_count') }}
                                </dt>
                                <dd class="mt-1 text-lg font-bold tabular-nums text-zinc-900 dark:text-white">{{ $fmtInt($personalOrdersCount) }}</dd>
                            </div>
                            <div class="px-4 py-3 text-center sm:px-6">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.my_sales_tickets') }}
                                </dt>
                                <dd class="mt-1 text-lg font-bold tabular-nums text-zinc-900 dark:text-white">{{ $fmtInt($personalTicketsCount) }}</dd>
                            </div>
                            <div class="px-4 py-3 text-center sm:px-6">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.my_sales_earnings') }}
                                </dt>
                                <dd class="mt-1 text-lg font-bold tabular-nums text-indigo-600 dark:text-indigo-400">{{ $fmt($personalCommission) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span></dd>
                            </div>
                        </dl>

                        <div class="px-2 py-2 sm:px-4">
                            @if(empty($personalTypeBreakdown['rows']))
                                <p class="px-3 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.no_sales') }}
                                </p>
                            @else
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            <th class="px-3 py-2 text-left">{{ __('promoter_managers.dashboard.overview.ticket_type_column') }}</th>
                                            <th class="px-3 py-2 text-right">{{ __('promoter_managers.dashboard.overview.quantity_column') }}</th>
                                            <th class="px-3 py-2 text-right">{{ __('promoter_managers.dashboard.overview.gross_column') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @foreach($personalTypeBreakdown['rows'] as $r)
                                            <tr>
                                                <td class="px-3 py-2 text-zinc-900 dark:text-white">{{ $r['name'] }}</td>
                                                <td class="px-3 py-2 text-right tabular-nums text-zinc-700 dark:text-zinc-200">{{ $fmtInt($r['quantity']) }}</td>
                                                <td class="px-3 py-2 text-right tabular-nums font-semibold text-zinc-900 dark:text-white">{{ $fmt($r['gross']) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </x-ui.card>

                    {{-- Prodaja promotera --}}
                    <x-ui.card class="!rounded-2xl">
                        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                    <x-ui.icon name="users" class="size-4" />
                                </span>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ __('promoter_managers.dashboard.overview.subs_sales') }}
                                </p>
                            </div>
                            <span class="text-2xl font-bold tabular-nums text-zinc-900 dark:text-white">
                                {{ $fmt((float) $subsTypeBreakdown['total_gross']) }}
                                <span class="text-xs font-medium text-zinc-500">RSD</span>
                            </span>
                        </div>

                        <dl class="grid grid-cols-3 divide-x divide-zinc-200 border-b border-zinc-200 dark:divide-zinc-800 dark:border-zinc-800">
                            <div class="px-4 py-3 text-center sm:px-6">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.subs_sales_count') }}
                                </dt>
                                <dd class="mt-1 text-lg font-bold tabular-nums text-zinc-900 dark:text-white">{{ $fmtInt($subsOrdersCount) }}</dd>
                            </div>
                            <div class="px-4 py-3 text-center sm:px-6">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.subs_sales_tickets') }}
                                </dt>
                                <dd class="mt-1 text-lg font-bold tabular-nums text-zinc-900 dark:text-white">{{ $fmtInt($subsTicketsCount) }}</dd>
                            </div>
                            <div class="px-4 py-3 text-center sm:px-6">
                                <dt class="text-[10px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.subs_sales_earnings') }}
                                </dt>
                                <dd class="mt-1 text-lg font-bold tabular-nums text-violet-600 dark:text-violet-400">{{ $fmt($subCommissionShare) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span></dd>
                            </div>
                        </dl>

                        <div class="px-2 py-2 sm:px-4">
                            @if(empty($subsTypeBreakdown['rows']))
                                <p class="px-3 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ __('promoter_managers.dashboard.overview.no_subs_sales') }}
                                </p>
                            @else
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                            <th class="px-3 py-2 text-left">{{ __('promoter_managers.dashboard.overview.ticket_type_column') }}</th>
                                            <th class="px-3 py-2 text-right">{{ __('promoter_managers.dashboard.overview.quantity_column') }}</th>
                                            <th class="px-3 py-2 text-right">{{ __('promoter_managers.dashboard.overview.gross_column') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                        @foreach($subsTypeBreakdown['rows'] as $r)
                                            <tr>
                                                <td class="px-3 py-2 text-zinc-900 dark:text-white">{{ $r['name'] }}</td>
                                                <td class="px-3 py-2 text-right tabular-nums text-zinc-700 dark:text-zinc-200">{{ $fmtInt($r['quantity']) }}</td>
                                                <td class="px-3 py-2 text-right tabular-nums font-semibold text-zinc-900 dark:text-white">{{ $fmt($r['gross']) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </x-ui.card>

                </div>
            </div>
        </section>

        {{-- ===================== Section 2 · LISTA PROMOTERA ===================== --}}
        <section>
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                    {{ __('promoter_managers.dashboard.subs_list.heading') }}
                </h2>
                <a href="https://prodaja.refest.rs/promoter-manager/sub-promoters"
                   target="_blank"
                   rel="noopener"
                   class="inline-flex items-center gap-2 self-start rounded-lg bg-white px-4 py-2 text-sm font-semibold text-zinc-700 ring-1 ring-inset ring-zinc-300 transition hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                    <x-ui.icon name="arrow-up-right" class="size-4" />
                    {{ __('promoter_managers.dashboard.manage_subs_button') }}
                </a>
            </div>

            @if($subsCombined->isEmpty())
                <x-ui.card>
                    <x-ui.empty-state
                        icon="users"
                        :title="__('promoter_managers.dashboard.subs_list.empty')"
                    />
                </x-ui.card>
            @else
                <x-ui.card :padding="false">
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('promoter_managers.dashboard.subs_list.header_name') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('promoter_managers.dashboard.subs_list.header_tickets') }}</x-ui.table-cell>
                                <x-ui.table-cell header class="hidden lg:table-cell">{{ __('promoter_managers.dashboard.subs_list.header_per_type') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric class="hidden md:table-cell">{{ __('promoter_managers.dashboard.subs_list.header_sub_earnings') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric class="hidden md:table-cell">{{ __('promoter_managers.dashboard.subs_list.header_manager_earn') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric class="hidden lg:table-cell">{{ __('promoter_managers.dashboard.subs_list.header_paid') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('promoter_managers.dashboard.subs_list.header_owed') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach($subsCombined as $row)
                                @php $sub = $row['user']; @endphp
                                <x-ui.table-row onclick="window.location='{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}'">
                                    <x-ui.table-cell>
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                {{ $sub->initials() }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $sub->name }}</p>
                                                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $sub->email }}</p>
                                            </div>
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        <span class="font-bold">{{ $fmtInt($row['tickets_sold']) }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="hidden lg:table-cell">
                                        @if(empty($row['per_type']))
                                            <span class="text-xs text-zinc-400">{{ __('promoter_managers.dashboard.subs_list.no_sales') }}</span>
                                        @else
                                            <div class="flex flex-col gap-1 text-xs">
                                                @foreach($row['per_type'] as $pt)
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span class="truncate text-zinc-700 dark:text-zinc-300">{{ $pt['name'] }}</span>
                                                        <span class="shrink-0 tabular-nums text-zinc-600 dark:text-zinc-400">
                                                            {{ $fmtInt($pt['quantity']) }} <span class="text-[10px] text-zinc-400">{{ __('promoter_managers.dashboard.subs_list.quantity_label') }}</span>
                                                            <span class="text-zinc-300 dark:text-zinc-600">·</span>
                                                            {{ $fmt($pt['gross']) }} <span class="text-[10px] text-zinc-400">RSD</span>
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                        <span class="text-violet-600 dark:text-violet-400">
                                            {{ $fmt($row['sub_commission']) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span>
                                        </span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                        <span class="text-emerald-600 dark:text-emerald-400">
                                            {{ $fmt($row['manager_commission']) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span>
                                        </span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric class="hidden lg:table-cell">
                                        <span class="text-zinc-700 dark:text-zinc-200">
                                            {{ $fmt($row['paid']) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span>
                                        </span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        @if($row['owed'] > 0)
                                            <span class="font-bold text-rose-600 dark:text-rose-400">{{ $fmt($row['owed']) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span></span>
                                        @elseif($row['owed'] < 0)
                                            <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                                                {{ __('promoter_managers.dashboard.subs_section.owe_negative') }} {{ $fmt(abs($row['owed'])) }} RSD
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                <x-ui.icon name="check" class="size-3.5" />
                                                {{ __('promoter_managers.dashboard.subs_section.owe_zero') }}
                                            </span>
                                        @endif
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </x-ui.table-body>
                    </x-ui.table>
                </x-ui.card>
            @endif
        </section>

        {{-- ===================== Section 3 · ISTORIJA TRANSAKCIJA ===================== --}}
        <section>
            <div class="mb-4">
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                    {{ __('promoter_managers.dashboard.transactions.heading') }}
                </h2>
            </div>

            {{-- Analitika --}}
            <x-ui.kpi-strip :cols="3" class="mb-4">
                <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        {{ __('promoter_managers.dashboard.transactions.analytics_cash') }}
                    </p>
                    <p class="mt-1 text-xl font-bold tabular-nums text-zinc-900 dark:text-white sm:text-2xl">
                        @if($cashInHand < 0)−@endif{{ $fmt(abs($cashInHand)) }} <span class="text-xs font-medium text-zinc-500">RSD</span>
                    </p>
                </div>
                <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ __('promoter_managers.dashboard.transactions.analytics_earnings') }}
                    </p>
                    <p class="mt-1 text-xl font-bold tabular-nums text-zinc-900 dark:text-white sm:text-2xl">
                        {{ $fmt($myEarnings) }} <span class="text-xs font-medium text-zinc-500">RSD</span>
                    </p>
                </div>
                <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-400">
                        {{ __('promoter_managers.dashboard.transactions.analytics_debt') }}
                    </p>
                    <p @class([
                        'mt-1 text-xl font-bold tabular-nums sm:text-2xl',
                        'text-rose-600 dark:text-rose-400' => $oweAmount > 0,
                        'text-zinc-900 dark:text-white' => $oweAmount <= 0,
                    ])>
                        {{ $fmt($oweAmount) }} <span class="text-xs font-medium text-zinc-500">RSD</span>
                    </p>
                </div>
            </x-ui.kpi-strip>

            {{-- Lista transakcija --}}
            @if($ledgerEntries->isEmpty())
                <x-ui.card>
                    <x-ui.empty-state
                        icon="banknotes"
                        :title="__('promoter_managers.dashboard.transactions.empty')"
                    />
                </x-ui.card>
            @else
                <x-ui.card :padding="false">
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('promoter_managers.dashboard.transactions.date') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('promoter_managers.dashboard.transactions.from') }} / {{ __('promoter_managers.dashboard.transactions.to') }}</x-ui.table-cell>
                                <x-ui.table-cell header class="hidden md:table-cell">{{ __('promoter_managers.dashboard.transactions.note') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('promoter_managers.dashboard.transactions.amount') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach($ledgerEntries as $tx)
                                <x-ui.table-row>
                                    <x-ui.table-cell nowrap>
                                        <span class="text-zinc-700 dark:text-zinc-200 tabular-nums">{{ $tx->paid_at->format('d M Y') }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="flex items-center gap-2">
                                            @if($tx->direction === 'in')
                                                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                                    <x-ui.icon name="arrow-down" class="size-3.5" />
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $tx->payer?->name ?? '—' }}</p>
                                                    <p class="text-[11px] uppercase tracking-wider text-emerald-600 dark:text-emerald-400">{{ __('promoter_managers.dashboard.transactions.from_sub') }}</p>
                                                </div>
                                            @else
                                                <span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300">
                                                    <x-ui.icon name="arrow-up-right" class="size-3.5" />
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $tx->recorder?->name ?? '—' }}</p>
                                                    <p class="text-[11px] uppercase tracking-wider text-rose-600 dark:text-rose-400">{{ __('promoter_managers.dashboard.transactions.to_org') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="hidden md:table-cell">
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                            @if($tx->note){{ $tx->note }}@else<span class="text-zinc-300 dark:text-zinc-700">—</span>@endif
                                        </span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        <span @class([
                                            'text-sm font-bold tabular-nums',
                                            'text-emerald-600 dark:text-emerald-400' => $tx->direction === 'in',
                                            'text-rose-600 dark:text-rose-400' => $tx->direction !== 'in',
                                        ])>
                                            {{ $fmtSigned($tx->amount_signed) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span>
                                        </span>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </x-ui.table-body>
                    </x-ui.table>
                </x-ui.card>
            @endif
        </section>

        {{-- ===================== Section 4 · ISTORIJA PRODATIH ULAZNICA ===================== --}}
        <section>
            <div class="mb-4">
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                    {{ __('promoter_managers.dashboard.ticket_history.heading') }}
                </h2>
            </div>

            @if($recentOrders->isEmpty())
                <x-ui.card>
                    <x-ui.empty-state
                        icon="ticket"
                        :title="__('promoter_managers.dashboard.ticket_history.empty')"
                    />
                </x-ui.card>
            @else
                <x-ui.card :padding="false">
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('promoter_managers.dashboard.ticket_history.header_seller') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('promoter_managers.dashboard.ticket_history.header_date') }}</x-ui.table-cell>
                                <x-ui.table-cell header class="hidden md:table-cell">{{ __('promoter_managers.dashboard.ticket_history.header_order') }}</x-ui.table-cell>
                                <x-ui.table-cell header class="hidden md:table-cell">{{ __('promoter_managers.dashboard.ticket_history.header_email') }}</x-ui.table-cell>
                                <x-ui.table-cell header>{{ __('promoter_managers.dashboard.ticket_history.header_items') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right" numeric>{{ __('promoter_managers.dashboard.ticket_history.header_manager') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach($recentOrders as $order)
                                @php
                                    $sellerName = $order->requestedBy?->name ?? '—';
                                    $sellerIsMe = $order->requested_by === $manager->id;
                                    $managerShare = (float) $order->commissionBeneficiaries
                                        ->where('beneficiary_user_id', $manager->id)
                                        ->where('beneficiary_role', 'promoter_manager')
                                        ->sum('commission_amount');
                                    $itemsCount = (int) $order->items->sum('quantity');
                                @endphp
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="flex items-center gap-2">
                                            <div @class([
                                                'flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                                'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' => $sellerIsMe,
                                                'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300' => ! $sellerIsMe,
                                            ])>
                                                {{ strtoupper(mb_substr($sellerName, 0, 1)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $sellerName }}</p>
                                                <p class="truncate text-[11px] text-zinc-500 dark:text-zinc-400">{{ $sellerIsMe ? __('promoter_managers.dashboard.overview.my_sales') : __('promoter_managers.dashboard.overview.subs_sales') }}</p>
                                            </div>
                                        </div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell nowrap>
                                        <span class="text-sm tabular-nums text-zinc-700 dark:text-zinc-200">{{ $order->created_at->format('d M Y H:i') }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="hidden md:table-cell">
                                        <span class="text-xs font-mono text-zinc-500 dark:text-zinc-400">{{ $order->order_number ?? '#'.$order->id }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="hidden md:table-cell">
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-[200px] inline-block">{{ $order->email ?: '—' }}</span>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell>
                                        <div class="flex flex-col gap-1 text-xs">
                                            @foreach($order->items as $item)
                                                <div class="flex items-center justify-between gap-3">
                                                    <span class="truncate text-zinc-700 dark:text-zinc-300">{{ $item->ticketType?->name ?? '—' }}</span>
                                                    <span class="shrink-0 tabular-nums text-zinc-600 dark:text-zinc-400">
                                                        {{ $fmtInt((int) $item->quantity) }} <span class="text-[10px] text-zinc-400">{{ __('promoter_managers.dashboard.ticket_history.quantity_label') }}</span>
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <p class="mt-1 text-[10px] uppercase tracking-wider text-zinc-400">
                                            {{ $fmtInt($itemsCount) }} {{ __('promoter_managers.dashboard.ticket_history.quantity_label') }}
                                        </p>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right" numeric>
                                        <span @class([
                                            'text-sm font-bold tabular-nums',
                                            'text-emerald-600 dark:text-emerald-400' => $managerShare > 0,
                                            'text-zinc-400 dark:text-zinc-500' => $managerShare == 0,
                                        ])>
                                            {{ $fmt($managerShare) }} <span class="text-[10px] font-normal text-zinc-500">RSD</span>
                                        </span>
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