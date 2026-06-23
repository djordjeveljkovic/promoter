<x-layouts.app :title="__('sub_promoter_dashboard.page_title')">
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
                        {{ __('sub_promoter_dashboard.eyebrow') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ __('sub_promoter_dashboard.main_heading') }}
                    </h1>
                    @if($manager)
                        <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.managed_by_prefix') }}
                            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $manager->name }}</span>
                            ({{ $manager->email }})
                        </p>
                    @else
                        <p class="mt-2 max-w-2xl text-sm text-yellow-700 dark:text-yellow-400">
                            {{ __('sub_promoter_dashboard.no_manager_notice') }}
                        </p>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('sub_promoter.orders.index') }}"
                       class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-200 dark:hover:bg-zinc-800">
                        {{ __('sub_promoter_dashboard.recent_orders.view_all_button') }}
                    </a>
                    <a href="{{ route('sub_promoter.orders.create') }}"
                       class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                        {{ __('sub_promoter_dashboard.recent_orders.new_order_button') }}
                    </a>
                </div>
            </header>

            {{-- ===================== Hero: what I owe to my manager ===================== --}}
            <section class="mb-8 sm:mb-10">
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
                                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-100">
                                        {{ __('sub_promoter_dashboard.financials.debt_overpaid_indicator') }}
                                    </span>
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
            <section class="mb-8 sm:mb-12">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.pyramid.heading') }}
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.pyramid.help') }}
                        </p>
                    </div>
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
                            <div class="hidden items-center justify-center text-gray-300 dark:text-gray-600 md:flex">
                                <flux:icon name="minus" class="size-6" />
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
                            <div class="hidden items-center justify-center text-gray-300 dark:text-gray-600 md:flex">
                                <flux:icon name="minus" class="size-6" />
                            </div>
                            {{-- Remaining --}}
                            <div class="rounded-lg border-2 {{ $amountOwedToManager > 0 ? 'border-rose-300 bg-rose-50 dark:border-rose-700/40 dark:bg-rose-900/20' : 'border-emerald-300 bg-emerald-50 dark:border-emerald-700/40 dark:bg-emerald-900/20' }} p-4">
                                <p class="text-xs font-medium uppercase tracking-wider {{ $amountOwedToManager > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                    {{ __('sub_promoter_dashboard.pyramid.row_amount_due') }}
                                </p>
                                <p class="mt-1 text-xl font-bold {{ $amountOwedToManager > 0 ? 'text-rose-900 dark:text-rose-100' : 'text-emerald-900 dark:text-emerald-100' }}">
                                    {{ number_format(max($amountOwedToManager, 0), 2) }} <span class="text-xs font-medium {{ $amountOwedToManager > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }}">RSD</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('sub_promoter_dashboard.pyramid.row_already_paid') }}
                                </p>
                                <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($amountAlreadyPaid, 2) }} RSD
                                </p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('sub_promoter_dashboard.pyramid.row_remaining') }}
                                </p>
                                <p class="mt-1 text-base font-semibold {{ $amountOwedToManager > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ number_format(max($amountOwedToManager, 0), 2) }} RSD
                                </p>
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('sub_promoter_dashboard.financials.commission_earned') }}
                                </p>
                                <p class="mt-1 text-base font-semibold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($subCommissionAllTime, 2) }} RSD
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== Record payment to manager ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.record_payment.heading') }}
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.record_payment.helper_text') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('sub_promoter.payments.to_manager.store') }}" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-12 sm:p-6">
                        @csrf
                        <div class="sm:col-span-3">
                            <label for="sub_pay_amount" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.record_payment.amount_label') }}
                            </label>
                            <input type="number" name="amount" id="sub_pay_amount" step="0.01" min="0.01" max="{{ max($amountOwedToManager, 0) > 0 ? $amountOwedToManager : 9999999.99 }}" required
                                   placeholder="0.00"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="sub_pay_paid_at" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.record_payment.paid_at_label') }}
                            </label>
                            <input type="date" name="paid_at" id="sub_pay_paid_at" value="{{ now()->toDateString() }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('paid_at') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-6">
                            <label for="sub_pay_note" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.record_payment.note_label') }}
                            </label>
                            <input type="text" name="note" id="sub_pay_note" maxlength="500"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('note') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-12 flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
                                <flux:icon name="banknotes" class="size-4" />
                                {{ __('sub_promoter_dashboard.record_payment.submit_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ===================== Financials KPI cards ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('sub_promoter_dashboard.financials.heading') }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.financials.commission_earned') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <flux:icon name="banknotes" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                {{ number_format($subCommissionAllTime, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.financials.all_time_label') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.financials.gross_sales') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                                <flux:icon name="chart-bar" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                {{ number_format($subGrossSalesAllTime, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.financials.gross_sales_subtext') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.financials.amount_paid') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <flux:icon name="credit-card" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400 sm:text-3xl">
                                {{ number_format($amountAlreadyPaid, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.financials.amount_owed_subtext') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.financials.commission_last_30') }}
                            </span>
                            <span class="inline-flex size-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <flux:icon name="arrow-trending-up" class="size-4" />
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                {{ number_format($subCommissionLast30Days, 2) }}
                            </span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">RSD</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.financials.commission_last_30_subtext') }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2">
                    <div class="flex items-center justify-between gap-4 bg-white p-4 dark:bg-zinc-900">
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.financials.gross_sales_last_30') }}
                            </span>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ number_format($subGrossSalesLast30Days, 2) }} RSD
                            </p>
                        </div>
                        <flux:icon name="calendar-days" class="size-5 text-gray-400 dark:text-gray-500" />
                    </div>
                    <div class="flex items-center justify-between gap-4 bg-white p-4 dark:bg-zinc-900">
                        <div>
                            <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.performance.orders_all_time') }}
                            </span>
                            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                {{ number_format($subOrdersAllTime) }}
                            </p>
                        </div>
                        <flux:icon name="ticket" class="size-5 text-gray-400 dark:text-gray-500" />
                    </div>
                </div>
            </section>

            {{-- ===================== Performance ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('sub_promoter_dashboard.performance.heading') }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.performance.orders_all_time') }}
                        </span>
                        <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ number_format($subOrdersAllTime) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.performance.tickets_all_time') }}
                        </span>
                        <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ number_format($subTicketsSoldAllTime) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.performance.orders_last_30') }}
                        </span>
                        <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ number_format($subOrdersLast30Days) }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                        <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.performance.tickets_last_30') }}
                        </span>
                        <span class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ number_format($subTicketsSoldLast30Days) }}
                        </span>
                    </div>
                </div>
            </section>

            {{-- ===================== Top tickets + Status breakdown ===================== --}}
            <section class="mb-8 sm:mb-12 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.top_tickets.heading') }}
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.top_tickets.help') }}
                        </p>
                    </div>
                    @if($subTicketTypePerformance->isEmpty())
                        <div class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.top_tickets.no_data') }}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.top_tickets.header_type') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.top_tickets.header_quantity') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.top_tickets.header_revenue') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($subTicketTypePerformance as $type)
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-900 dark:text-white sm:px-6">
                                                {{ $type->name }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-200 sm:px-6">
                                                {{ number_format($type->total_quantity_sold) }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white sm:px-6">
                                                {{ number_format($type->total_revenue_generated, 2) }} RSD
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.status_breakdown.heading') }}
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.status_breakdown.help') }}
                        </p>
                    </div>
                    @if($subOrderStatusCounts->isEmpty())
                        <div class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.status_breakdown.empty') }}
                        </div>
                    @else
                        <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
                            @php
                                $statusTotal = (int) $subOrderStatusCounts->sum();
                            @endphp
                            @foreach($subOrderStatusCounts as $status => $count)
                                @php
                                    $statusKey = $status ?? 'unknown';
                                    $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                                    if ($statusText === 'orders.statuses.' . $statusKey) {
                                        $statusText = \Illuminate\Support\Str::ucfirst($statusKey);
                                    }
                                    $statusClass = $jobStatusColors[$statusKey] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
                                    $pct = $statusTotal > 0 ? round(((int) $count) / $statusTotal * 100) : 0;
                                @endphp
                                <li class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $count }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $pct }}%)</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            {{-- ===================== Commission split (set by manager) ===================== --}}
            @if(!empty($overrides))
                <section class="mb-8 sm:mb-12">
                    <div class="mb-4 flex items-end justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.commission_split.heading') }}
                        </h2>
                    </div>
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('sub_promoter_dashboard.commission_split.help') }}
                            </p>
                        </div>
                        <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
                            @foreach($overrides as $typeId => $ov)
                                @php
                                    $type = \App\Models\TicketType::find($typeId);
                                    $mode = is_array($ov) ? ($ov['type'] ?? 'percentage') : 'percentage';
                                @endphp
                                <li class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
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
                    </div>
                </section>
            @endif

            {{-- ===================== Payment history ===================== --}}
            <section class="mb-8 sm:mb-12">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('sub_promoter_dashboard.payment_history.heading') }}
                    </h2>
                </div>
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.payment_history.sub_heading') }}
                        </p>
                    </div>
                    @if($recentPayments->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.payment_history.empty') }}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.payment_history.date') }}
                                        </th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.payment_history.direction') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.payment_history.amount') }}
                                        </th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.payment_history.note') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($recentPayments as $payment)
                                        @php
                                            $isSent = $payment->payer_id === $sub->id;
                                        @endphp
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-900 dark:text-white sm:px-6">
                                                {{ $payment->paid_at->format('d M Y') }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 dark:text-gray-200 sm:px-6">
                                                @if($isSent)
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">
                                                        <flux:icon name="arrow-up-right" class="size-3" />
                                                        {{ __('sub_promoter_dashboard.payment_history.direction_to') }} {{ $payment->receiver?->name ?? '—' }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                        <flux:icon name="arrow-down-left" class="size-3" />
                                                        {{ $payment->payer?->name ?? '—' }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold sm:px-6 {{ $isSent ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                                {{ $isSent ? '−' : '+' }} {{ number_format((float) $payment->amount, 2) }} RSD
                                            </td>
                                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
                                                {{ $payment->note ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ===================== Recent orders ===================== --}}
            <section class="mb-8">
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.recent_orders.heading') }}
                        </h2>
                    </div>
                    <a href="{{ route('sub_promoter.orders.index') }}"
                       class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-gray-200 dark:hover:bg-zinc-800">
                        {{ __('sub_promoter_dashboard.recent_orders.view_all_button') }}
                    </a>
                </div>

                @if($recentOrders->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800">
                            <flux:icon name="ticket" class="size-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('sub_promoter_dashboard.recent_orders.empty_title') }}
                        </h3>
                        <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                            {{ __('sub_promoter_dashboard.recent_orders.empty') }}
                        </p>
                        <a href="{{ route('sub_promoter.orders.create') }}"
                           class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            <flux:icon name="plus" class="size-4" />
                            {{ __('sub_promoter_dashboard.recent_orders.new_order_button') }}
                        </a>
                    </div>
                @else
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.recent_orders.header_order') }}
                                        </th>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.recent_orders.header_customer') }}
                                        </th>
                                        <th class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.recent_orders.header_total') }}
                                        </th>
                                        <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">
                                            {{ __('sub_promoter_dashboard.recent_orders.header_status') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($recentOrders as $order)
                                        @php
                                            $statusKey = $order->job_status ?? 'unknown';
                                            $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                                            if ($statusText === 'orders.statuses.' . $statusKey) {
                                                $statusText = \Illuminate\Support\Str::ucfirst($order->job_status ?? __('orders.statuses.unknown'));
                                            }
                                            $statusClass = $jobStatusColors[$order->job_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
                                        @endphp
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-900 dark:text-white sm:px-6">
                                                <a href="{{ route('promoter.orders.show', $order->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                                    #{{ $order->order_number }}
                                                </a>
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-500 dark:text-gray-300 sm:px-6">
                                                {{ $order->email }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white sm:px-6">
                                                {{ number_format($order->total, 2) }} RSD
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-center sm:px-6">
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusClass }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
