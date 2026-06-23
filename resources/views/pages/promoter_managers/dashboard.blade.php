<x-layouts.app :title="__('promoter_managers.dashboard.page_title')">
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

            {{-- ===================== Page Header ===================== --}}
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
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
                        <flux:icon name="users" class="size-4" />
                        {{ __('promoter_managers.dashboard.my_subs.manage_button') }}
                    </a>
                    <a href="{{ route('promoter.orders.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                        <flux:icon name="ticket" class="size-4" />
                        {{ __('promoter_managers.dashboard.new_order_button') }}
                    </a>
                </div>
            </header>

            {{-- ===================== Hero: amount I owe organizers ===================== --}}
            <section class="mb-8 sm:mb-10">
                <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 p-6 text-white shadow-lg sm:p-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-indigo-100">
                                {{ __('promoter_managers.dashboard.pay_organizers.heading') }}
                            </p>
                            <p class="mt-1 text-sm text-indigo-100/90 max-w-2xl">
                                {{ __('promoter_managers.dashboard.pay_organizers.sub_heading') }}
                            </p>
                            <div class="mt-4 flex flex-wrap items-baseline gap-3">
                                @if($debtSummary['amount_owed_to_organizers'] > 0)
                                    <span class="text-4xl font-bold tracking-tight sm:text-5xl">
                                        {{ number_format($debtSummary['amount_owed_to_organizers'], 2) }}
                                    </span>
                                    <span class="text-lg font-medium text-indigo-100/90">RSD</span>
                                @else
                                    <span class="text-4xl font-bold tracking-tight text-emerald-200 sm:text-5xl">
                                        {{ number_format(abs($debtSummary['amount_owed_to_organizers']), 2) }} RSD
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-100">
                                        {{ $debtSummary['amount_owed_to_organizers'] < 0
                                            ? __('promoter_managers.dashboard.team_debts.owe_negative')
                                            : __('promoter_managers.dashboard.team_debts.owe_zero') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm sm:max-w-md sm:grid-cols-3">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.my_financials.gross_sales') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($debtSummary['gross_sales'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.my_financials.commission_earned') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($debtSummary['manager_commission'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.my_financials.sub_commission_total') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($debtSummary['sub_commissions'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.my_financials.amount_already_paid') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($debtSummary['amount_already_paid_to_organizers'], 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.quick_stats.team_owed_to_me') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($teamOwedToManager, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.quick_stats.team_paid_to_me') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($teamAlreadyPaidToManager, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== KPI cards ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.dashboard.my_financials.heading') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.my_financials.commission_earned') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <flux:icon name="banknotes" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                {{ number_format($debtSummary['manager_commission'], 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.all_time_label') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.my_financials.gross_sales') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                                <flux:icon name="chart-bar" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                {{ number_format($debtSummary['gross_sales'], 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.gross_sales_subtext') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.my_financials.amount_owed') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md {{ $debtSummary['amount_owed_to_organizers'] > 0 ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' }}">
                                <flux:icon name="{{ $debtSummary['amount_owed_to_organizers'] > 0 ? 'arrow-up-right' : 'arrow-down-right' }}" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight sm:text-3xl {{ $debtSummary['amount_owed_to_organizers'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ number_format(abs($debtSummary['amount_owed_to_organizers']), 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.amount_owed_subtext') }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.my_financials.commission_last_30') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <flux:icon name="arrow-trending-up" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                {{ number_format($managerCommissionLast30Days, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.commission_last_30_subtext') }}
                        </p>
                    </div>
                </div>
            </section>

            {{-- ===================== Quick stats strip ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.dashboard.quick_stats.heading') }}
                    </h2>
                </div>
                <div class="grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 lg:grid-cols-5">
                    <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.quick_stats.subs_count') }}
                        </span>
                        <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ $subDebts->count() }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.quick_stats.team_gross') }}
                        </span>
                        <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ number_format($debtSummary['gross_sales'], 2) }}<span class="ml-1 text-sm font-medium text-gray-500">RSD</span>
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.quick_stats.team_commission') }}
                        </span>
                        <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            {{ number_format($debtSummary['manager_commission'] + $debtSummary['sub_commissions'], 2) }}<span class="ml-1 text-sm font-medium text-gray-500">RSD</span>
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.quick_stats.team_owed_to_me') }}
                        </span>
                        <span class="text-2xl font-bold tracking-tight {{ $teamOwedToManager > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ number_format($teamOwedToManager, 2) }}<span class="ml-1 text-sm font-medium text-gray-500">RSD</span>
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5 col-span-2 lg:col-span-1">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.quick_stats.team_paid_to_me') }}
                        </span>
                        <span class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">
                            {{ number_format($teamAlreadyPaidToManager, 2) }}<span class="ml-1 text-sm font-medium text-gray-500">RSD</span>
                        </span>
                    </div>
                </div>
            </section>

            {{-- ===================== Pay organizers form ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.pay_organizers.heading') }}
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.pay_organizers.helper_text') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('promoter_manager.payments.to_organizers.store') }}" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-12 sm:p-6">
                        @csrf
                        <div class="sm:col-span-3">
                            <label for="pay_org_amount" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.pay_organizers.amount_label') }}
                            </label>
                            <input type="number" name="amount" id="pay_org_amount" step="0.01" min="0.01" required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="pay_org_paid_at" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.pay_organizers.paid_at_label') }}
                            </label>
                            <input type="date" name="paid_at" id="pay_org_paid_at" value="{{ now()->toDateString() }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('paid_at') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-6">
                            <label for="pay_org_note" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.pay_organizers.note_label') }}
                            </label>
                            <input type="text" name="note" id="pay_org_note" maxlength="500"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('note') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-12 flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
                                <flux:icon name="banknotes" class="size-4" />
                                {{ __('promoter_managers.dashboard.pay_organizers.submit_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ===================== Team debts: per-sub-promoter cards ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.team_debts.heading') }}
                        </h2>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.team_debts.sub_heading') }}
                        </p>
                    </div>
                    <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 sm:w-auto">
                        <flux:icon name="plus" class="size-4" />
                        {{ __('promoter_managers.dashboard.my_subs.add_button') }}
                    </a>
                </div>

                @if($subDebts->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800">
                            <flux:icon name="users" class="size-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.team_debts.empty') }}
                        </h3>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($subDebts as $row)
                            @php
                                $sub = $row['user'];
                                $owed = $row['amount_owed_to_manager'];
                                $paid = $row['amount_already_paid'];
                                $gross = $row['gross_sales'];
                                $subComm = $row['sub_commission'];
                            @endphp
                            <div class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                                <div class="flex items-center gap-3 border-b border-gray-200 px-5 py-4 dark:border-zinc-800">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                        {{ $sub->initials() }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $sub->name }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $sub->email }}</p>
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                        {{ $sub->sub_orders_count ?? 0 }} {{ __('promoter_managers.dashboard.my_subs.orders_unit') }}
                                    </span>
                                </div>
                                <div class="px-5 py-4">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.team_debts.card_gross') }}</p>
                                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ number_format($gross, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.team_debts.card_sub_commission') }}</p>
                                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ number_format($subComm, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.team_debts.card_paid') }}</p>
                                            <p class="mt-0.5 font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($paid, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.team_debts.card_owed') }}</p>
                                            @if($owed > 0)
                                                <p class="mt-0.5 text-lg font-bold text-rose-600 dark:text-rose-400">{{ number_format($owed, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                            @elseif($owed < 0)
                                                <p class="mt-0.5 text-sm font-semibold text-amber-600 dark:text-amber-400">
                                                    {{ __('promoter_managers.dashboard.team_debts.owe_negative') }} {{ number_format(abs($owed), 2) }} RSD
                                                </p>
                                            @else
                                                <p class="mt-0.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                    {{ __('promoter_managers.dashboard.team_debts.owe_zero') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <form method="POST" action="{{ route('promoter_manager.payments.from_sub.store', $sub->id) }}" class="flex-1 grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto] sm:items-end">
                                        @csrf
                                        <div>
                                            <label for="amt-{{ $sub->id }}" class="sr-only">{{ __('promoter_managers.dashboard.team_debts.record_payment_button') }}</label>
                                            <input type="number" name="amount" id="amt-{{ $sub->id }}" step="0.01" min="0.01" max="{{ max($owed, 0) > 0 ? $owed : 9999999.99 }}" required
                                                   placeholder="0.00"
                                                   class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2" />
                                        </div>
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                            <flux:icon name="plus-circle" class="size-4" />
                                            <span class="hidden sm:inline">{{ __('promoter_managers.dashboard.team_debts.record_payment_button') }}</span>
                                            <span class="sm:hidden">{{ __('promoter_managers.dashboard.team_debts.record_payment_button') }}</span>
                                        </button>
                                    </form>
                                    <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}"
                                       class="inline-flex items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-200 dark:hover:bg-zinc-700">
                                        <flux:icon name="pencil-square" class="size-4" />
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ===================== Top sub-promoters leaderboard ===================== --}}
            @if($topSubs->isNotEmpty())
                <section class="mb-8 sm:mb-12">
                    <div class="mb-4 flex flex-col gap-2">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.top_subs.heading') }}
                        </h2>
                        <p class="max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.top_subs.sub_heading') }}
                        </p>
                    </div>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.top_subs.header_rank') }}
                                        </th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.top_subs.header_name') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.top_subs.header_orders') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.top_subs.header_gross') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.top_subs.header_commission') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('promoter_managers.dashboard.top_subs.header_owed') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($topSubs as $idx => $row)
                                        @php
                                            $sub = $row['user'];
                                            $rank = $idx + 1;
                                            $badgeBg = match(true) {
                                                $rank === 1 => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
                                                $rank === 2 => 'bg-zinc-200 text-zinc-700 dark:bg-zinc-600 dark:text-zinc-200',
                                                $rank === 3 => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
                                                default     => 'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300',
                                            };
                                        @endphp
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                            <td class="whitespace-nowrap px-5 py-3 sm:px-6">
                                                <span class="inline-flex size-7 items-center justify-center rounded-full text-xs font-bold {{ $badgeBg }}">
                                                    {{ $rank }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-sm sm:px-6">
                                                <div class="flex items-center gap-2">
                                                    <div class="flex size-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                        {{ $sub->initials() }}
                                                    </div>
                                                    <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}" class="font-medium text-gray-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                                        {{ $sub->name }}
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-200 sm:px-6">
                                                {{ $sub->sub_orders_count ?? 0 }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white sm:px-6">
                                                {{ number_format($row['gross_sales'], 2) }} RSD
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm text-emerald-600 dark:text-emerald-400 sm:px-6">
                                                {{ number_format($row['sub_commission'], 2) }} RSD
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold sm:px-6 {{ $row['amount_owed_to_manager'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                {{ number_format($row['amount_owed_to_manager'], 2) }} RSD
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ===================== Payment history ===================== --}}
            <section class="mb-8 sm:mb-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- From sub-promoters --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.payment_history.from_subs_heading') }}
                        </h2>
                    </div>
                    @if($recentPaymentsFromSubs->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.payment_history.from_subs_empty') }}
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
                                                @if($payment->note)
                                                    · {{ $payment->note }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="shrink-0 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                            + {{ number_format((float) $payment->amount, 2) }} RSD
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- To organizers --}}
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.payment_history.to_organizers_heading') }}
                        </h2>
                    </div>
                    @if($recentPaymentsToOrganizers->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.payment_history.to_organizers_empty') }}
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
                                            @if($payment->note)
                                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $payment->note }}</p>
                                            @endif
                                        </div>
                                        <span class="shrink-0 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                            − {{ number_format((float) $payment->amount, 2) }} RSD
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

        </div>
    </div>
</x-layouts.app>
