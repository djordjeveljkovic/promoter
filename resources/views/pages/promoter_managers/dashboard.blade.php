<x-layouts.app :title="__('promoter_managers.dashboard.page_title')">
    @php
        // Helpers used by the view. Kept tiny so the template stays
        // readable.
        $fmt = fn (float $v) => number_format($v, 2);

        // --- KPI inputs ----------------------------------------------------
        $myEarnings    = (float) $earningsBreakdown['total_commission'];
        $cashInHand    = (float) $cashInHand;
        $moneyWithSubs = (float) $teamOwedToManager;
        $oweAmount     = (float) $debtSummary['amount_owed_to_organizers'];

        // --- "My earnings" breakdown --------------------------------------
        $personalCommission  = (float) $earningsBreakdown['personal_commission'];
        $subCommissionShare  = (float) $earningsBreakdown['sub_commission'];
        $personalGross       = (float) $earningsBreakdown['personal_gross'];
        $subsGrossEarnings   = (float) $earningsBreakdown['subs_gross'];

        // --- "Debt to organizers" breakdown -------------------------------
        $myGross           = (float) $debtSummary['manager_gross_sales'];
        $myCommissionDebt  = (float) $debtSummary['manager_commission'];
        $subsGrossDebt     = (float) $debtSummary['subs_gross_sales'];
        $subsCommission    = (float) $debtSummary['sub_commissions'];
        $alreadyPaidDebt   = (float) $debtSummary['amount_already_paid_to_organizers'];

        // --- Overview numbers ---------------------------------------------
        $totalReceivedFromSubs = (float) $teamAlreadyPaidToManager;
        $totalPaidToOrganizers = $alreadyPaidDebt;
        $personalComm30 = (float) $personal['commission_last_30_days'];
    @endphp

    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">

            {{-- ===================== Flash messages ===================== --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-200">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-5" />
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">
                    <div class="flex items-center gap-2">
                        <flux:icon name="exclamation-circle" class="size-5" />
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- ===================== Header ===================== --}}
            <header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ __('promoter_managers.dashboard.eyebrow') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ __('promoter_managers.dashboard.main_heading') }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        {{ __('promoter_managers.dashboard.sub_heading') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        <flux:icon name="users" class="size-4" />
                        {{ __('promoter_managers.dashboard.subs_section.empty_cta') }}
                    </a>
                    <a href="{{ route('promoter.orders.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                        <flux:icon name="ticket" class="size-4" />
                        {{ __('promoter_managers.dashboard.new_order_button') }}
                    </a>
                </div>
            </header>

            {{-- ===================== Section 1 · 4 KPI HERO CARDS ===================== --}}
            <section class="mb-8 sm:mb-10" aria-label="{{ __('promoter_managers.dashboard.main_heading') }}">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                    {{-- 1) Moja zarada — scrolls down to #earnings --}}
                    <a href="#earnings"
                       class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 p-5 text-white shadow-lg transition hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                                {{ __('promoter_managers.dashboard.kpi.my_earnings.label') }}
                            </p>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                                <flux:icon name="banknotes" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">{{ $fmt($myEarnings) }}</span>
                            <span class="text-sm font-medium text-white/80">RSD</span>
                        </div>
                        <p class="text-xs text-white/85">
                            {{ __('promoter_managers.dashboard.kpi.my_earnings.help') }}
                        </p>
                        <span class="mt-auto inline-flex items-center gap-1 text-xs font-medium text-white/90">
                            {{ __('promoter_managers.dashboard.kpi.my_earnings.scroll_hint') }}
                            <flux:icon name="arrow-down" class="size-3 transition group-hover:translate-y-0.5" />
                        </span>
                    </a>

                    {{-- 2) Novac kod mene — informational --}}
                    <div class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 p-5 text-white shadow-lg sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                                {{ __('promoter_managers.dashboard.kpi.cash_in_hand.label') }}
                            </p>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                                <flux:icon name="shopping-bag" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">
                                @if($cashInHand < 0)−@endif{{ $fmt(abs($cashInHand)) }}
                            </span>
                            <span class="text-sm font-medium text-white/80">RSD</span>
                        </div>
                        <p class="text-xs text-white/85">
                            {{ __('promoter_managers.dashboard.kpi.cash_in_hand.help') }}
                        </p>
                        @if($cashInHand < 0)
                            <p class="mt-auto text-xs font-medium text-white/95">
                                {{ __('promoter_managers.dashboard.kpi.cash_in_hand.overpaid') }}
                            </p>
                        @endif
                    </div>

                    {{-- 3) Novac kod promotera — links to sub-promoters page --}}
                    <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                       class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 p-5 text-white shadow-lg transition hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                                {{ __('promoter_managers.dashboard.kpi.money_with_promoters.label') }}
                            </p>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                                <flux:icon name="users" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold tracking-tight tabular-nums sm:text-4xl">{{ $fmt($moneyWithSubs) }}</span>
                            <span class="text-sm font-medium text-white/80">RSD</span>
                        </div>
                        <p class="text-xs text-white/85">
                            {{ __('promoter_managers.dashboard.kpi.money_with_promoters.help') }}
                        </p>
                        <span class="mt-auto inline-flex items-center gap-1 text-xs font-medium text-white/95">
                            {{ __('promoter_managers.dashboard.kpi.money_with_promoters.open_link') }}
                            <flux:icon name="arrow-up-right" class="size-3 transition group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                        </span>
                    </a>

                    {{-- 4) Dug organizatorima — scrolls to debts section --}}
                    <a href="#debts"
                       class="group relative flex flex-col gap-3 overflow-hidden rounded-2xl
                              @if($oweAmount > 0) bg-gradient-to-br from-rose-500 via-rose-600 to-orange-600
                              @elseif($oweAmount < 0) bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600
                              @else bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 @endif
                              p-5 text-white shadow-lg transition hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950 sm:p-6">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-wider text-white/80">
                                {{ __('promoter_managers.dashboard.kpi.debt_to_organizers.label') }}
                            </p>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white">
                                <flux:icon name="currency-dollar" class="size-4" />
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
                        <p class="text-xs text-white/85">
                            {{ __('promoter_managers.dashboard.kpi.debt_to_organizers.help') }}
                        </p>
                        <span class="mt-auto inline-flex items-center gap-1 text-xs font-medium text-white/95">
                            {{ __('promoter_managers.dashboard.kpi.debt_to_organizers.scroll_hint') }}
                            <flux:icon name="arrow-down" class="size-3 transition group-hover:translate-y-0.5" />
                        </span>
                    </a>
                </div>
            </section>

            {{-- ===================== Section 2 · MY EARNINGS (breakdown) ===================== --}}
            <section id="earnings" class="mb-8 scroll-mt-24 sm:mb-10">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.earnings_section.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.earnings_section.sub_heading') }}
                        </p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    {{-- Two-column breakdown of personal vs. team share --}}
                    <div class="grid grid-cols-1 divide-y divide-gray-200 dark:divide-zinc-800 lg:grid-cols-2 lg:divide-x lg:divide-y-0">
                        {{-- Personal column --}}
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                        {{ __('promoter_managers.dashboard.earnings_section.my_commission') }}
                                    </p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight tabular-nums text-gray-900 dark:text-white sm:text-4xl">
                                        {{ $fmt($personalCommission) }} <span class="text-base font-medium text-gray-500">RSD</span>
                                    </p>
                                </div>
                                <span class="inline-flex size-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    <flux:icon name="user" class="size-5" />
                                </span>
                            </div>
                            <dl class="mt-5 grid grid-cols-1 gap-3 text-sm">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-zinc-800">
                                    <dt class="text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.earnings_section.personal_gross') }}</dt>
                                    <dd class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $fmt($personalGross) }} RSD</dd>
                                </div>
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-zinc-800">
                                    <dt class="text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.earnings_section.my_commission') }}</dt>
                                    <dd class="font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($personalCommission) }} RSD</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.earnings_section.recent_commission') }}</dt>
                                    <dd class="text-sm font-semibold tabular-nums text-indigo-600 dark:text-indigo-400">{{ $fmt($personalComm30) }} RSD</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Team share column --}}
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wider text-violet-600 dark:text-violet-400">
                                        {{ __('promoter_managers.dashboard.earnings_section.sub_commission_share') }}
                                    </p>
                                    <p class="mt-2 text-3xl font-bold tracking-tight tabular-nums text-gray-900 dark:text-white sm:text-4xl">
                                        {{ $fmt($subCommissionShare) }} <span class="text-base font-medium text-gray-500">RSD</span>
                                    </p>
                                </div>
                                <span class="inline-flex size-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                    <flux:icon name="users" class="size-5" />
                                </span>
                            </div>
                            <dl class="mt-5 grid grid-cols-1 gap-3 text-sm">
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-zinc-800">
                                    <dt class="text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.earnings_section.subs_gross') }}</dt>
                                    <dd class="font-semibold tabular-nums text-gray-900 dark:text-white">{{ $fmt($subsGrossEarnings) }} RSD</dd>
                                </div>
                                <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-zinc-800">
                                    <dt class="text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.earnings_section.sub_commission_share') }}</dt>
                                    <dd class="font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($subCommissionShare) }} RSD</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.at_a_glance.subs_commission') }}</dt>
                                    <dd class="text-sm font-semibold tabular-nums text-violet-600 dark:text-violet-400">{{ $fmt($teamCommissionTotal) }} RSD</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Total earned strip --}}
                    <div class="flex items-center justify-between border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-zinc-800 dark:bg-zinc-800/40 sm:px-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                            {{ __('promoter_managers.dashboard.earnings_section.total_earned') }}
                        </p>
                        <p class="text-2xl font-bold tabular-nums text-gray-900 dark:text-white sm:text-3xl">
                            {{ $fmt($myEarnings) }} <span class="text-sm font-medium text-gray-500">RSD</span>
                        </p>
                    </div>
                </div>
            </section>

            {{-- ===================== Section 3 · TOP SUB-PROMOTERS (leaderboard) ===================== --}}
            <section class="mb-8 sm:mb-10">
                <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.leaderboard.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.leaderboard.sub_heading') }}
                        </p>
                    </div>
                    @if($topSubs->isNotEmpty())
                        <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                           class="inline-flex items-center gap-1.5 self-start text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            {{ __('promoter_managers.dashboard.kpi.money_with_promoters.open_link') }}
                            <flux:icon name="arrow-up-right" class="size-3.5" />
                        </a>
                    @endif
                </div>

                @if($topSubs->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800">
                            <flux:icon name="chart-bar" class="size-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.leaderboard.empty') }}
                        </h3>
                        <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                           class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            <flux:icon name="plus" class="size-4" />
                            {{ __('promoter_managers.dashboard.subs_section.empty_cta') }}
                        </a>
                    </div>
                @else
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_rank') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_name') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_orders') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 lg:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_tickets') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_gross') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 md:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_sub_commission') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 md:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_manager_commission') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 lg:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_paid') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.leaderboard.header_owed') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($topSubs as $i => $row)
                                        @php
                                            $sub    = $row['user'];
                                            $gross  = (float) $row['gross_sales'];
                                            $subC   = (float) $row['sub_commission'];
                                            $mgrC   = (float) $row['manager_commission'];
                                            $paid   = (float) $row['amount_already_paid'];
                                            $owed   = (float) $row['amount_owed_to_manager'];
                                        @endphp
                                        <tr class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-zinc-800/50"
                                            onclick="window.location='{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}'">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-500 sm:px-6">
                                                @if($i === 0)
                                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">1</span>
                                                @elseif($i === 1)
                                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-zinc-200 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">2</span>
                                                @elseif($i === 2)
                                                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300">3</span>
                                                @else
                                                    <span class="inline-flex size-7 items-center justify-center text-gray-500 dark:text-gray-400">{{ $i + 1 }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 sm:px-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                        {{ $sub->initials() }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $sub->name }}</p>
                                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $sub->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200 sm:table-cell sm:px-6">
                                                {{ number_format((int) $row['orders_count']) }}
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200 lg:table-cell sm:px-6">
                                                {{ number_format((int) $row['tickets_sold']) }}
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold tabular-nums text-gray-900 dark:text-white sm:px-6">
                                                {{ $fmt($gross) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-violet-600 dark:text-violet-400 md:table-cell sm:px-6">
                                                {{ $fmt($subC) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-emerald-600 dark:text-emerald-400 md:table-cell sm:px-6">
                                                {{ $fmt($mgrC) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200 lg:table-cell sm:px-6">
                                                {{ $fmt($paid) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-6">
                                                @if($owed > 0)
                                                    <span class="text-sm font-bold tabular-nums text-rose-600 dark:text-rose-400">{{ $fmt($owed) }} <span class="text-xs font-normal text-gray-500">RSD</span></span>
                                                @elseif($owed < 0)
                                                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                                                        {{ __('promoter_managers.dashboard.subs_section.owe_negative') }} {{ $fmt(abs($owed)) }} RSD
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                        <flux:icon name="check" class="size-3.5" />
                                                        {{ __('promoter_managers.dashboard.subs_section.owe_zero') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </section>

            {{-- ===================== Section 4 · DEBTS & PAYMENTS (overview + list) ===================== --}}
            <section id="debts" class="mb-8 scroll-mt-24 sm:mb-10">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.dashboard.debts_section.heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('promoter_managers.dashboard.debts_section.sub_heading') }}
                    </p>
                </div>

                {{-- 4.1 Overview --}}
                <div class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.debts_section.overview_heading') }}
                        </h3>
                    </div>
                    <dl class="grid grid-cols-1 divide-y divide-gray-200 dark:divide-zinc-800 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4 lg:divide-x">
                        <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                    {{ __('promoter_managers.dashboard.debts_section.overview_subs_owe') }}
                                </dt>
                                <dd class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white sm:text-2xl">{{ $fmt($moneyWithSubs) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                                <flux:icon name="users" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                    {{ __('promoter_managers.dashboard.debts_section.overview_subs_paid') }}
                                </dt>
                                <dd class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white sm:text-2xl">{{ $fmt($totalReceivedFromSubs) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <flux:icon name="banknotes" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-rose-600 dark:text-rose-400">
                                    {{ __('promoter_managers.dashboard.debts_section.overview_orgs_owe') }}
                                </dt>
                                <dd class="mt-1 text-xl font-bold tabular-nums {{ $oweAmount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }} sm:text-2xl">{{ $fmt($oweAmount) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400">
                                <flux:icon name="currency-dollar" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-4 sm:px-6">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                    {{ __('promoter_managers.dashboard.debts_section.overview_orgs_paid') }}
                                </dt>
                                <dd class="mt-1 text-xl font-bold tabular-nums text-gray-900 dark:text-white sm:text-2xl">{{ $fmt($totalPaidToOrganizers) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <flux:icon name="check-circle" class="size-4" />
                            </span>
                        </div>
                    </dl>

                    {{-- Formula (no minus sign on "Already paid" — described plainly) --}}
                    <div class="border-t border-gray-200 bg-gray-50 px-5 py-4 text-xs dark:border-zinc-800 dark:bg-zinc-800/40 sm:px-6">
                        <p class="font-semibold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                            {{ __('promoter_managers.dashboard.owe_hero.breakdown_eyebrow') }}
                        </p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">
                            {{ __('promoter_managers.dashboard.owe_hero.breakdown_my_gross') }}
                            <span class="font-semibold text-gray-900 dark:text-white">+ {{ $fmt($myGross) }}</span>
                            ·
                            {{ __('promoter_managers.dashboard.owe_hero.breakdown_subs_gross') }}
                            <span class="font-semibold text-gray-900 dark:text-white">+ {{ $fmt($subsGrossDebt) }}</span>
                            ·
                            {{ __('promoter_managers.dashboard.owe_hero.breakdown_my_commission') }}
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">− {{ $fmt($myCommissionDebt) }}</span>
                            ·
                            {{ __('promoter_managers.dashboard.owe_hero.breakdown_subs_commission') }}
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">− {{ $fmt($subsCommission) }}</span>
                        </p>
                        <p class="mt-1 text-gray-600 dark:text-gray-300">
                            {{ __('promoter_managers.dashboard.owe_hero.breakdown_paid') }}
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $fmt($alreadyPaidDebt) }} RSD</span>
                            <span class="text-gray-400">·</span>
                            <span class="font-semibold text-rose-600 dark:text-rose-400">= {{ $fmt($oweAmount) }} RSD</span>
                        </p>
                    </div>
                </div>

                {{-- 4.2 Per-sub list --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('promoter_managers.dashboard.debts_section.list_heading') }}
                            </h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.debts_section.list_sub_heading') }}
                            </p>
                        </div>
                        <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                            {{ __('promoter_managers.dashboard.debts_section.open_sub_promoters') }}
                            <flux:icon name="arrow-up-right" class="size-3.5" />
                        </a>
                    </div>

                    @if($subDebts->isEmpty())
                        <div class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.debts_section.empty') }}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.debts_section.header_sub') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 md:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.debts_section.header_gross') }}
                                        </th>
                                        <th scope="col" class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 lg:table-cell sm:px-6">
                                            {{ __('promoter_managers.dashboard.debts_section.header_sub_commission') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.debts_section.header_paid') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.debts_section.header_owed') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            <span class="sr-only">{{ __('promoter_managers.dashboard.debts_section.record_payment') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($subDebts as $row)
                                        @php
                                            $sub  = $row['user'];
                                            $owed = (float) $row['amount_owed_to_manager'];
                                            $paid = (float) $row['amount_already_paid'];
                                            $gross = (float) $row['gross_sales'];
                                            $subComm = (float) $row['sub_commission'];
                                        @endphp
                                        <tr class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-zinc-800/50"
                                            onclick="window.location='{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}'">
                                            <td class="px-4 py-3 sm:px-6">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                        {{ $sub->initials() }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $sub->name }}</p>
                                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $sub->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-gray-700 dark:text-gray-200 md:table-cell sm:px-6">
                                                {{ $fmt($gross) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="hidden whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-violet-600 dark:text-violet-400 lg:table-cell sm:px-6">
                                                {{ $fmt($subComm) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm tabular-nums text-emerald-600 dark:text-emerald-400 sm:px-6">
                                                {{ $fmt($paid) }} <span class="text-xs font-normal text-gray-500">RSD</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-6" onclick="event.stopPropagation()">
                                                @if($owed > 0)
                                                    <span class="text-sm font-bold tabular-nums text-rose-600 dark:text-rose-400">{{ $fmt($owed) }} <span class="text-xs font-normal text-gray-500">RSD</span></span>
                                                @elseif($owed < 0)
                                                    <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">
                                                        {{ __('promoter_managers.dashboard.subs_section.owe_negative') }} {{ $fmt(abs($owed)) }} RSD
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                        <flux:icon name="check" class="size-3.5" />
                                                        {{ __('promoter_managers.dashboard.subs_section.owe_zero') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-6" onclick="event.stopPropagation()">
                                                <form method="POST" action="{{ route('promoter_manager.payments.from_sub.store', $sub->id) }}" class="inline-flex items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="redirect_to" value="manager_edit" />
                                                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ max($owed, 0) > 0 ? $owed : 9999999.99 }}" required
                                                           placeholder="{{ __('promoter_managers.dashboard.debts_section.amount_placeholder') }}"
                                                           class="block w-24 rounded-md border-gray-300 bg-white text-right text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-1.5" />
                                                    <button type="submit"
                                                            class="inline-flex shrink-0 items-center justify-center gap-1 rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                                                        <flux:icon name="plus-circle" class="size-3.5" />
                                                        <span class="hidden sm:inline">{{ __('promoter_managers.dashboard.debts_section.record_payment') }}</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ===================== Section 5 · PAYMENT EVIDENCE ===================== --}}
            <section class="mb-8 sm:mb-10">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.dashboard.evidence.heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('promoter_managers.dashboard.evidence.sub_heading') }}
                    </p>
                </div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- From sub-promoters --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ __('promoter_managers.dashboard.evidence.from_subs_heading') }}
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.dashboard.evidence.from_subs_subtext') }}
                                </p>
                            </div>
                            <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                               class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                {{ __('promoter_managers.dashboard.evidence.view_full_sub_promoters') }}
                                <flux:icon name="arrow-up-right" class="size-3" />
                            </a>
                        </div>
                        @if($recentPaymentsFromSubs->isEmpty())
                            <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.evidence.from_subs_empty') }}
                            </div>
                        @else
                            <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
                                @foreach($recentPaymentsFromSubs as $payment)
                                    <li class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $payment->payer?->name ?? '—' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $payment->paid_at->format('d M Y') }}
                                                    · {{ __('promoter_managers.dashboard.evidence.recorded_by') }}: {{ $payment->recorder?->name ?? '—' }}
                                                    @if($payment->note)
                                                        · {{ $payment->note }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-sm font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">
                                                + {{ $fmt((float) $payment->amount) }} RSD
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- To organizers --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ __('promoter_managers.dashboard.evidence.to_orgs_heading') }}
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.dashboard.evidence.to_orgs_subtext') }}
                                </p>
                            </div>
                        </div>
                        @if($recentPaymentsToOrganizers->isEmpty())
                            <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.evidence.to_orgs_empty') }}
                            </div>
                        @else
                            <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
                                @foreach($recentPaymentsToOrganizers as $payment)
                                    <li class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $payment->paid_at->format('d M Y') }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('promoter_managers.dashboard.evidence.recorded_by') }}: {{ $payment->recorder?->name ?? '—' }}
                                                    @if($payment->note)
                                                        · {{ $payment->note }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-sm font-semibold tabular-nums text-indigo-600 dark:text-indigo-400">
                                                {{ $fmt((float) $payment->amount) }} RSD
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

        </div>
    </div>

    {{-- ===================== Smooth scroll for hash links ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('a[href^="#"]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    var targetId = this.getAttribute('href');
                    if (targetId.length > 1) {
                        var target = document.querySelector(targetId);
                        if (target) {
                            e.preventDefault();
                            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            history.pushState(null, '', targetId);
                        }
                    }
                });
            });
        });
    </script>
</x-layouts.app>
