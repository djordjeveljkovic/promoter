<x-layouts.app :title="__('promoter_managers.dashboard.page_title')">
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">

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

            {{-- ===================== My Financials ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.dashboard.my_financials.heading') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- KPI 1: Commission earned --}}
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
                                {{ number_format($managerCommissionAllTime, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.all_time_label') }}
                        </p>
                    </div>

                    {{-- KPI 2: Gross sales --}}
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
                                {{ number_format($managerGrossSalesAllTime, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.gross_sales_subtext') }}
                        </p>
                    </div>

                    {{-- KPI 3: Amount owed --}}
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.my_financials.amount_owed') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md {{ $amountOwed >= 0 ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' }}">
                                <flux:icon name="{{ $amountOwed >= 0 ? 'arrow-up-right' : 'arrow-down-right' }}" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight sm:text-3xl {{ $amountOwed >= 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ number_format(abs($amountOwed), 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_financials.amount_owed_subtext') }}
                        </p>
                    </div>

                    {{-- KPI 4: Commission last 30 days --}}
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

            {{-- ===================== Team Overview ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.dashboard.team_overview.heading') }}
                    </h2>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        {{ $subPromoters->count() }}
                        {{ __('promoter_managers.dashboard.team_overview.subs_unit') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 md:grid-cols-3">
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.team_overview.subs_count') }}
                        </span>
                        <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ $subPromoters->count() }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.team_overview.sub_gross_sales') }}
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                                {{ number_format($subSalesAllTime, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.team_overview.sub_commissions') }}
                        </span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                                {{ number_format($subCommissionsAllTime, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== Sub-Promoters List ===================== --}}
            <section>
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.my_subs.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_subs.sub_heading') }}
                        </p>
                    </div>
                    <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 sm:w-auto">
                        <flux:icon name="plus" class="size-4" />
                        {{ __('promoter_managers.dashboard.my_subs.add_button') }}
                    </a>
                </div>

                @if($subPromoters->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800">
                            <flux:icon name="users" class="size-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.my_subs.empty_title') }}
                        </h3>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.my_subs.empty_description') }}
                        </p>
                        <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                           class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            <flux:icon name="plus" class="size-4" />
                            {{ __('promoter_managers.dashboard.my_subs.add_button') }}
                        </a>
                    </div>
                @else
                    {{-- Mobile list (cards) --}}
                    <ul role="list" class="space-y-3 md:hidden">
                        @foreach($subPromoters as $sub)
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-white p-4 ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                        {{ $sub->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $sub->name }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $sub->email }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ ($sub->sub_orders_count ?? 0) . ' ' . __('promoter_managers.dashboard.my_subs.orders_unit') }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}"
                                   class="shrink-0 rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400"
                                   title="{{ __('promoter_managers.dashboard.my_subs.edit_action') }}">
                                    <flux:icon name="pencil-square" class="size-5" />
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Desktop table --}}
                    <div class="hidden overflow-hidden rounded-xl ring-1 ring-gray-200 dark:ring-zinc-800 md:block">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                            <thead class="bg-gray-50 dark:bg-zinc-900/50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('promoter_managers.dashboard.my_subs.header_name') }}
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('promoter_managers.dashboard.my_subs.header_email') }}
                                    </th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('promoter_managers.dashboard.my_subs.header_orders') }}
                                    </th>
                                    <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                        {{ __('promoter_managers.dashboard.my_subs.header_actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                @foreach($subPromoters as $sub)
                                    <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                        <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                    {{ $sub->initials() }}
                                                </div>
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $sub->name }}</span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $sub->email }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $sub->sub_orders_count ?? 0 }}
                                        </td>
                                        <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm">
                                            <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}"
                                               class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 font-medium text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10">
                                                {{ __('promoter_managers.dashboard.my_subs.edit_action') }}
                                                <flux:icon name="arrow-right" class="size-3.5" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

        </div>
    </div>
</x-layouts.app>
