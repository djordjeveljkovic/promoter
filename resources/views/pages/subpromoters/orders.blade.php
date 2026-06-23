<x-layouts.app :title="__('sub_promoter_dashboard.orders.page_title')">
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">

            <header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ __('sub_promoter_dashboard.eyebrow') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ __('sub_promoter_dashboard.orders.main_heading') }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        {{ __('sub_promoter_dashboard.orders.sub_heading') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('sub_promoter.dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                        {{ __('sub_promoter_dashboard.orders.back_to_dashboard') }}
                    </a>
                    <a href="{{ route('sub_promoter.orders.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
                        <flux:icon name="plus" class="size-4" />
                        {{ __('sub_promoter_dashboard.orders.new_order_button') }}
                    </a>
                </div>
            </header>

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700 dark:bg-green-700 dark:text-green-100">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-700 dark:text-red-100">
                    {{ session('error') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_order') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_customer') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_date') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_items') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_total') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_my_commission') }}
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_status') }}
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('sub_promoter_dashboard.orders.table.header_actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                            @forelse ($orders as $order)
                                @php
                                    $statusKey = $order->job_status ?? 'unknown';
                                    $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                                    if ($statusText === 'orders.statuses.' . $statusKey) {
                                        $statusText = \Illuminate\Support\Str::ucfirst($order->job_status ?? __('orders.statuses.unknown'));
                                    }
                                    $statusClass = $jobStatusColors[$order->job_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
                                    $myCommission = $commissionsByOrder[$order->id] ?? 0.0;
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">#{{ $order->order_number }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $order->email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-300">{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                        @foreach($order->items as $item)
                                            {{ $item->quantity }}x {{ $item->ticketType->name }}@if(!$loop->last)<br>@endif
                                        @endforeach
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-300">
                                        {{ number_format($order->total, 2) }} RSD
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-emerald-700 dark:text-emerald-400">
                                        {{ number_format($myCommission, 2) }} RSD
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <span class="inline-flex lowercase items-center px-2 text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-center">
                                        <a href="{{ route('promoter.orders.show', $order->id) }}"
                                           class="inline-flex items-center justify-center px-2 py-1 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-400 rounded-md transition-colors">
                                            {{ __('sub_promoter_dashboard.orders.table.actions_view_button') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('sub_promoter_dashboard.orders.table.empty') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="border-t border-gray-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900 sm:px-6">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
