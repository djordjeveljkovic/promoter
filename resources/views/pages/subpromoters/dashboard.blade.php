<x-layouts.app :title="__('sub_promoter_dashboard.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">{{ __('sub_promoter_dashboard.main_heading') }}</h1>
        @if($manager)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
                {{ __('sub_promoter_dashboard.managed_by_prefix') }}
                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $manager->name }}</span>
                ({{ $manager->email }})
            </p>
        @else
            <p class="text-sm text-yellow-700 dark:text-yellow-400 mb-8">{{ __('sub_promoter_dashboard.no_manager_notice') }}</p>
        @endif

        <section class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('sub_promoter_dashboard.stats.heading') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('sub_promoter_dashboard.stats.commission_earned') }}</h3>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($subCommissionAllTime, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('sub_promoter_dashboard.stats.orders') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $subOrdersAllTime }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <h3 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase">{{ __('sub_promoter_dashboard.stats.tickets_sold') }}</h3>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white mt-1">{{ $subTicketsSoldAllTime }}</p>
                </div>
            </div>
        </section>

        @if(!empty($overrides))
            <section class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">{{ __('sub_promoter_dashboard.commission_split.heading') }}</h2>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('sub_promoter_dashboard.commission_split.help') }}</p>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($overrides as $typeId => $ov)
                            @php
                                $type = \App\Models\TicketType::find($typeId);
                                $mode = is_array($ov) ? ($ov['type'] ?? 'percentage') : 'percentage';
                            @endphp
                            <li class="py-2 flex items-center justify-between">
                                <span class="text-sm text-gray-800 dark:text-white">{{ $type?->name ?? __('sub_promoter_dashboard.commission_split.unknown_type') }}</span>
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

        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">{{ __('sub_promoter_dashboard.recent_orders.heading') }}</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('sub_promoter.orders.index') }}"
                       class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        {{ __('sub_promoter_dashboard.recent_orders.view_all_button') }}
                    </a>
                    <a href="{{ route('sub_promoter.orders.create') }}"
                       class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        {{ __('sub_promoter_dashboard.recent_orders.new_order_button') }}
                    </a>
                </div>
            </div>

            @if($orders->isEmpty())
                <p class="text-gray-500 dark:text-gray-400">{{ __('sub_promoter_dashboard.recent_orders.empty') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('sub_promoter_dashboard.recent_orders.header_customer') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('sub_promoter_dashboard.recent_orders.header_total') }}</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">{{ __('sub_promoter_dashboard.recent_orders.header_status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($orders as $order)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">{{ $order->id }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300">{{ $order->email }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-300 text-right">{{ number_format($order->total, 2) }}</td>
                                    <td class="px-4 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $jobStatusColors[$order->job_status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order->job_status }}
                                        </span>
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
