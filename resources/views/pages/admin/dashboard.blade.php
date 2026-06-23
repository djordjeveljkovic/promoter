<x-layouts.app :title="__('dashboard.page_title')">
    @php
        // Override status colors with dark-mode-aware styles for the badges.
        $statusColors = [
            'processing' => 'bg-blue-50 text-blue-700 ring-blue-700/10 dark:bg-blue-500/10 dark:text-blue-400 dark:ring-blue-500/20',
            'failed'     => 'bg-rose-50 text-rose-700 ring-rose-700/10 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
            'blocked'    => 'bg-zinc-100 text-zinc-700 ring-zinc-700/10 dark:bg-zinc-500/10 dark:text-zinc-300 dark:ring-zinc-500/20',
            'completed'  => 'bg-emerald-50 text-emerald-700 ring-emerald-700/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
            'sent'       => 'bg-teal-50 text-teal-700 ring-teal-700/10 dark:bg-teal-500/10 dark:text-teal-400 dark:ring-teal-500/20',
            'pending'    => 'bg-amber-50 text-amber-700 ring-amber-700/10 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
        ];
    @endphp

    <div class="space-y-6 sm:space-y-8">

        {{-- ============================================================
             PAGE HEADER
        ============================================================ --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-3xl">
                    {{ __('dashboard.main_heading') }}
                </h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('dashboard.subtitle') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-700/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    </span>
                    {{ __('dashboard.system_status_active') }}
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600 ring-1 ring-inset ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:ring-zinc-700">
                    <flux:icon.calendar-days class="h-3.5 w-3.5" />
                    <time datetime="{{ now()->toDateString() }}">{{ now()->format('M d, Y') }}</time>
                </span>
            </div>
        </header>

        {{-- ============================================================
             OVERALL PERFORMANCE — STAT CARDS
        ============================================================ --}}
        <section aria-labelledby="overall-performance-heading">
            <h2 id="overall-performance-heading" class="sr-only">{{ __('dashboard.overall_performance.heading') }}</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Total Revenue (All Time) --}}
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('dashboard.overall_performance.total_revenue_all_time') }}
                        </p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-700/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                            <flux:icon.currency-dollar class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-3xl">
                        {{ number_format($totalRevenueAllTime, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                        {{ __('dashboard.all_time_label') }}
                    </p>
                </div>

                {{-- Total Orders (All Time) --}}
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('dashboard.overall_performance.total_orders_all_time') }}
                        </p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-700/10 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/20">
                            <flux:icon.shopping-bag class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-3xl">
                        {{ number_format($totalOrdersAllTime) }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                        {{ __('dashboard.all_time_label') }}
                    </p>
                </div>

                {{-- Tickets Sold (Completed Orders) --}}
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('dashboard.overall_performance.tickets_sold_completed_all_time') }}
                        </p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-600 ring-1 ring-inset ring-violet-700/10 dark:bg-violet-500/10 dark:text-violet-400 dark:ring-violet-500/20">
                            <flux:icon.ticket class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-3xl">
                        {{ number_format($totalTicketsEffectivelySoldAllTime) }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                        {{ __('dashboard.completed_orders_label') }}
                    </p>
                </div>

                {{-- Revenue (Last 30 Days) --}}
                <div class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-zinc-300 hover:shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 dark:hover:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('dashboard.overall_performance.revenue_last_30_days') }}
                        </p>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600 ring-1 ring-inset ring-amber-700/10 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20">
                            <flux:icon.arrow-trending-up class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="mt-4 text-2xl font-semibold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-3xl">
                        {{ number_format($totalRevenueLast30Days, 2) }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
                        {{ __('dashboard.last_30_days_label') }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ============================================================
             TOP TICKET TYPES  +  USER / ORDER STATS
        ============================================================ --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Top Ticket Types (2/3 width on desktop) --}}
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/60 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('dashboard.top_ticket_types.heading') }}
                        </h2>
                    </div>
                    <span class="hidden text-xs text-zinc-500 dark:text-zinc-400 sm:inline">
                        {{ $ticketTypePerformance->count() }}
                        {{ __('dashboard.entries_label') }}
                    </span>
                </div>

                @if($ticketTypePerformance->isEmpty())
                    <div class="flex flex-col items-center justify-center px-5 py-14 text-center">
                        <flux:icon.ticket class="h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                        <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('dashboard.top_ticket_types.no_data') }}
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead>
                                <tr class="bg-zinc-50/60 dark:bg-zinc-900/30">
                                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('dashboard.top_ticket_types.table_header_type_name') }}
                                    </th>
                                    <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('dashboard.top_ticket_types.table_header_quantity_sold') }}
                                    </th>
                                    <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        {{ __('dashboard.top_ticket_types.table_header_est_revenue') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach($ticketTypePerformance as $type)
                                    <tr class="transition-colors hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40">
                                        <td class="px-5 py-3.5 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $type->name }}
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm tabular-nums text-zinc-700 dark:text-zinc-300">
                                            {{ number_format($type->total_quantity_sold) }}
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm tabular-nums font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($type->total_revenue, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Right column: User & Ticket Stats + Order Statuses --}}
            <div class="flex flex-col gap-6">

                {{-- User & Ticket Stats --}}
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('dashboard.user_ticket_stats.heading') }}
                        </h2>
                    </div>
                    <div class="space-y-3 p-5">
                        @forelse($userCountsByRole as $role => $count)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">
                                    {{ ucfirst($role) }}{{ __('dashboard.user_ticket_stats.role_count_suffix') }}
                                </span>
                                <span class="font-semibold tabular-nums text-zinc-900 dark:text-zinc-100">
                                    {{ $count }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('dashboard.no_data_short') }}
                            </p>
                        @endforelse

                        @if($userCountsByRole->isNotEmpty())
                            <hr class="border-zinc-200 dark:border-zinc-800">
                        @endif

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">
                                {{ __('dashboard.user_ticket_stats.active_tickets') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-semibold tabular-nums text-emerald-700 dark:text-emerald-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                {{ $activeTicketsCount }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">
                                {{ __('dashboard.user_ticket_stats.inactive_tickets') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5 font-semibold tabular-nums text-rose-700 dark:text-rose-400">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                {{ $inactiveTicketsCount }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Order Statuses --}}
                <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('dashboard.order_statuses.heading') }}
                        </h2>
                    </div>
                    <div class="space-y-2.5 p-5">
                        @forelse($orderStatusCounts as $status => $count)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-zinc-600 dark:text-zinc-400">
                                    {{ ucfirst($status) }}
                                </span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium tabular-nums ring-1 ring-inset {{ $statusColors[$status] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-700/10 dark:bg-zinc-500/10 dark:text-zinc-300 dark:ring-zinc-500/20' }}">
                                    {{ $count }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ __('dashboard.no_data_short') }}
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================
             TOP PROMOTER PERFORMANCE
        ============================================================ --}}
        <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/60">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('dashboard.top_promoter_performance.heading') }}
                    </h2>
                </div>
                <span class="hidden shrink-0 text-xs text-zinc-500 dark:text-zinc-400 sm:inline">
                    {{ __('dashboard.top_5_label') }}
                </span>
            </div>

            @if($promoterPerformance->isEmpty() || $promoterPerformance->every(fn($p) => $p->total_orders_generated == 0))
                <div class="flex flex-col items-center justify-center px-5 py-14 text-center">
                    <flux:icon.user-group class="h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('dashboard.top_promoter_performance.no_data') }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead>
                            <tr class="bg-zinc-50/60 dark:bg-zinc-900/30">
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.top_promoter_performance.table_header_promoter') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.top_promoter_performance.table_header_email') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.top_promoter_performance.table_header_orders_generated') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.top_promoter_performance.table_header_revenue_generated') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($promoterPerformance as $promoter)
                                @if($promoter->total_orders_generated > 0)
                                    <tr class="transition-colors hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40">
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-semibold text-white shadow-sm">
                                                    {{ strtoupper(mb_substr($promoter->name, 0, 1)) }}
                                                </span>
                                                <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $promoter->name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                            {{ $promoter->email }}
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm tabular-nums text-zinc-700 dark:text-zinc-300">
                                            {{ number_format($promoter->total_orders_generated) }}
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm tabular-nums font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ number_format($promoter->total_revenue_generated, 2) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- ============================================================
             RECENT ORDERS
        ============================================================ --}}
        <section class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900/60">
            <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800">
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('dashboard.recent_orders.heading') }}
                    </h2>
                </div>
                <span class="hidden shrink-0 text-xs text-zinc-500 dark:text-zinc-400 sm:inline">
                    {{ $recentOrders->count() }}
                    {{ __('dashboard.entries_label') }}
                </span>
            </div>

            @if($recentOrders->isEmpty())
                <div class="flex flex-col items-center justify-center px-5 py-14 text-center">
                    <flux:icon.shopping-bag class="h-8 w-8 text-zinc-300 dark:text-zinc-600" />
                    <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('dashboard.recent_orders.no_data') }}
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead>
                            <tr class="bg-zinc-50/60 dark:bg-zinc-900/30">
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_order_id') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_customer_email') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_promoter') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_items') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_total') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_status') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                    {{ __('dashboard.recent_orders.table_header_date') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($recentOrders as $order)
                                <tr class="transition-colors hover:bg-zinc-50/60 dark:hover:bg-zinc-900/40">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <a href="{{-- route('admin.orders.show', $order->id) --}}" class="font-mono text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                        {{ $order->email }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm text-zinc-700 dark:text-zinc-300">
                                        {{ $order->requestedBy->name ?? 'N/A' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-sm text-zinc-600 dark:text-zinc-400">
                                        <div class="flex flex-col gap-0.5">
                                            @foreach($order->items as $item)
                                                <span class="whitespace-nowrap">{{ $item->quantity }}× {{ $item->ticketType->name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm tabular-nums font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ number_format($order->total, 2) }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusColors[$order->job_status] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-700/10 dark:bg-zinc-500/10 dark:text-zinc-300 dark:ring-zinc-500/20' }}">
                                            {{ ucfirst($order->job_status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-sm tabular-nums text-zinc-500 dark:text-zinc-400">
                                        <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('M d, Y H:i') }}</time>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>