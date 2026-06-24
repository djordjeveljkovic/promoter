<x-layouts.app :title="__('sub_promoter_dashboard.orders.page_title')">
    <div class="space-y-6">

        <x-ui.page-header
            :eyebrow="__('sub_promoter_dashboard.eyebrow')"
            :title="__('sub_promoter_dashboard.orders.main_heading')"
            :subtitle="__('sub_promoter_dashboard.orders.sub_heading')"
        >
            <x-slot:actions>
                <x-ui.button variant="secondary" :href="route('sub_promoter.dashboard')">
                    {{ __('sub_promoter_dashboard.orders.back_to_dashboard') }}
                </x-ui.button>
                <x-ui.button variant="primary" :href="route('sub_promoter.orders.create')" icon="plus">
                    {{ __('sub_promoter_dashboard.orders.new_order_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('sub_promoter_dashboard.orders.table.header_order') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('sub_promoter_dashboard.orders.table.header_customer') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('sub_promoter_dashboard.orders.table.header_date') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('sub_promoter_dashboard.orders.table.header_items') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('sub_promoter_dashboard.orders.table.header_total') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('sub_promoter_dashboard.orders.table.header_my_commission') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('sub_promoter_dashboard.orders.table.header_status') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('sub_promoter_dashboard.orders.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($orders as $order)
                        @php $myCommission = $commissionsByOrder[$order->id] ?? 0.0; @endphp
                        <x-ui.table-row>
                            <x-ui.table-cell nowrap>
                                <span class="font-medium text-zinc-900 dark:text-white">#{{ $order->order_number }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap>
                                <span class="text-zinc-500 dark:text-zinc-300">{{ $order->email }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap>
                                <span class="text-zinc-500 dark:text-zinc-300">{{ $order->created_at->format('M d, Y H:i') }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <span class="text-zinc-500 dark:text-zinc-300">
                                    @foreach($order->items as $item)
                                        {{ $item->quantity }}x {{ $item->ticketType->name }}@if(!$loop->last)<br>@endif
                                    @endforeach
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>
                                {{ number_format($order->total, 2) }} RSD
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>
                                <span class="font-semibold text-emerald-700 dark:text-emerald-400">
                                    {{ number_format($myCommission, 2) }} RSD
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                <x-ui.status-pill :status="$order->job_status ?? 'unknown'" />
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                <x-ui.link variant="primary" size="sm" :href="route('promoter.orders.show', $order->id)">
                                    {{ __('sub_promoter_dashboard.orders.table.actions_view_button') }}
                                </x-ui.link>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="8">
                                <x-ui.empty-state
                                    icon="ticket"
                                    :title="__('sub_promoter_dashboard.orders.table.empty')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>

            @if ($orders->hasPages())
                <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900 sm:px-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
</content>
</invoke>