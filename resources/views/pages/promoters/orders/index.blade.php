<x-layouts.app :title="__('orders.page_title')">
    <div class="space-y-6">

        <x-ui.page-header :title="__('orders.main_heading')">
            @if(auth()->user()?->isSupreme())
                <x-slot:eyebrow>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                        </svg>
                        {{ __('orders.private_banner') }}
                    </span>
                </x-slot:eyebrow>
            @endif
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('promoter.orders.create')" icon="plus">
                    {{ __('orders.create_new_order_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Flash Messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
        @if (session('info'))
            <x-ui.alert variant="info">{{ session('info') }}</x-ui.alert>
        @endif

        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('orders.table.header_order_id') }}</x-ui.table-cell>
                        @if(!empty($subIds))
                            <x-ui.table-cell header>{{ __('orders.table.header_seller') }}</x-ui.table-cell>
                        @endif
                        <x-ui.table-cell header>{{ __('orders.table.header_customer_email') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('orders.table.header_order_date') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('orders.table.header_items') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('orders.table.header_total_price') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" numeric>{{ __('orders.table.header_commission_earned') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('orders.table.header_job_status') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('orders.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($orders as $order)
                        @php
                            $isJobFailed = $order->job_status === 'failed';
                            $statusKey = $order->job_status ?? 'unknown';
                            $hasFailureReason = $isJobFailed && !empty($order->job_failure_reason);
                            $myCommission = $viewerCommissionByOrder[$order->id] ?? null;
                        @endphp
                        <x-ui.table-row>
                            <x-ui.table-cell nowrap>
                                <span class="font-medium text-zinc-900 dark:text-white">#{{ $order->order_number }}</span>
                            </x-ui.table-cell>
                            @if(!empty($subIds))
                                @php $sellerInfo = $sellerLabelsByOrder[$order->id] ?? null; @endphp
                                <x-ui.table-cell nowrap>
                                    @if($sellerInfo && $sellerInfo['is_self'])
                                        <x-ui.badge variant="indigo" size="sm">
                                            {{ __('orders.seller_self_badge') }}
                                        </x-ui.badge>
                                    @else
                                        <span class="text-zinc-500 dark:text-zinc-300">{{ $sellerInfo['name'] ?? __('orders.seller_unknown') }}</span>
                                    @endif
                                </x-ui.table-cell>
                            @endif
                            <x-ui.table-cell nowrap>
                                <span class="text-zinc-500 dark:text-zinc-300">{{ $order->email }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap>
                                <span class="text-zinc-500 dark:text-zinc-300">{{ $order->created_at->format('M d, Y H:i') }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <span class="text-zinc-500 dark:text-zinc-300">
                                    @foreach($order->items as $item)
                                        {{ $item->quantity }}x {{ $item->ticketType->name }}<br>
                                    @endforeach
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>{{ number_format($order->total, 2) }} RSD</x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>
                                @if (in_array($order->job_status, ['completed', 'sent']) && $myCommission !== null)
                                    <span class="font-semibold text-emerald-700 dark:text-emerald-400">{{ number_format($myCommission, 2) }} RSD</span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-500">{{ __('orders.table.commission_not_calculated') }}</span>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <span @class([
                                    'inline-flex items-center gap-1',
                                    'job-status-trigger cursor-pointer' => $hasFailureReason,
                                ])
                                    @if($hasFailureReason)
                                        data-target-row="error-row-{{ $order->id }}"
                                        title="{{ __('orders.table.status_error_tooltip_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}"
                                    @endif
                                >
                                    <x-ui.status-pill :status="$statusKey" />
                                    @if($hasFailureReason)
                                        <svg class="ml-0.5 w-3 h-3 transform transition-transform duration-150 status-icon" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                <div class="flex flex-col items-center gap-1">
                                    <x-ui.link variant="primary" size="sm" :href="route('promoter.orders.show', $order->id)">
                                        {{ __('orders.table.actions_view_button') }}
                                    </x-ui.link>
                                    @if ($order->job_status === 'failed')
                                        <form action="{{ route('orders.rerunImageJob', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-amber-600 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-200"
                                                    title="{{ __('orders.table.actions_retry_images_tooltip_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}">
                                                {{ __('orders.table.actions_retry_images_button') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-200"
                                                    title="{{ __('orders.table.actions_retry_email_tooltip_prefix') }} {{ Str::limit($order->job_failure_reason, 100) }}">
                                                {{ __('orders.table.actions_retry_email_button') }}
                                            </button>
                                        </form>
                                    @elseif (in_array($order->job_status, ['completed', 'sent']))
                                        <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                    class="text-xs text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-200"
                                                    title="{{ __('orders.table.actions_resend_email_tooltip') }}">
                                                {{ __('orders.table.actions_resend_email_button') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </x-ui.table-cell>
                        </x-ui.table-row>

                        {{-- Row for displaying the error message --}}
                        @if($hasFailureReason)
                            <x-ui.table-row :hover="false" id="error-row-{{ $order->id }}" class="error-message-row bg-rose-50 dark:bg-rose-900/20" style="display: none;">
                                <x-ui.table-cell colspan="{{ !empty($subIds) ? 9 : 8 }}" class="!px-6 !py-3">
                                    <div class="text-sm text-rose-700 dark:text-rose-200">
                                        <strong class="font-semibold block mb-1">{{ __('orders.table.job_failure_reason_label') }}</strong>
                                        <pre class="whitespace-pre-wrap text-xs font-mono p-2 bg-rose-100 dark:bg-rose-700 dark:text-rose-100 rounded border border-rose-200 dark:border-rose-600">{{ $order->job_failure_reason }}</pre>
                                    </div>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endif
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="{{ !empty($subIds) ? 9 : 8 }}">
                                <x-ui.empty-state
                                    icon="shopping-bag"
                                    :title="__('orders.table.no_orders_message')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>

            @if ($orders->hasPages())
                <div class="mt-6 px-4 sm:px-6 pb-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Pure JavaScript for toggling error messages --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusTriggers = document.querySelectorAll('.job-status-trigger');
            statusTriggers.forEach(trigger => {
                trigger.addEventListener('click', function () {
                    const targetRowId = this.dataset.targetRow;
                    if (!targetRowId) return;
                    const errorRow = document.getElementById(targetRowId);
                    const icon = this.querySelector('.status-icon');
                    if (errorRow) {
                        const isHidden = errorRow.style.display === 'none' || errorRow.style.display === '';
                        errorRow.style.display = isHidden ? 'table-row' : 'none';
                        if (icon) {
                            icon.classList.toggle('rotate-180', isHidden);
                        }
                    }
                });
            });
        });
    </script>
</x-layouts.app>
</content>
</invoke>