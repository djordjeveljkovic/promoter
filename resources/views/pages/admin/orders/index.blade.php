@php
    use App\Support\Status;
    // Status => Tailwind class map fed to the Alpine `orderStatusBoard` Echo
    // subscriber below. Keeps the live-update badge colours consistent with
    // the <x-ui.status-pill> server-render. The controller-side
    // $jobStatusColors variable is removed in Step 37.
    $statusClassMap = [];
    foreach (Status::VARIANTS as $key => $variant) {
        $statusClassMap[$key] = Status::classes($key);
    }
@endphp

<x-layouts.app :title="__('admin_orders.page_title')">
    <div
        class="space-y-6"
        x-data="orderStatusBoard(@js($statusClassMap))"
        x-init="init()"
    >
        <x-ui.page-header :title="__('admin_orders.main_heading')">
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('promoter.orders.create')" icon="plus">
                    {{ __('admin_orders.create_order_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.filter-form :action="route('admin.orders.index')" autosubmit>
            <x-ui.select
                name="status_filter"
                autosubmit
                :placeholder="__('admin_orders.filters.all_job_statuses_option')"
            >
                @foreach(Status::VARIANTS as $statusKey => $variant)
                    @if(!in_array($statusKey, ['N/A', 'failed_clickable'])) {{-- Internal keys, not for display name --}}
                        @php
                            $translated = __('admin_orders.statuses.' . $statusKey, [], app()->getLocale());
                        @endphp
                        <option value="{{ $statusKey }}" {{ request('status_filter') == $statusKey ? 'selected' : '' }}>
                            {{ $translated !== 'admin_orders.statuses.' . $statusKey ? $translated : Illuminate\Support\Str::ucfirst($statusKey) }}
                        </option>
                    @endif
                @endforeach
            </x-ui.select>

            {{-- Per-promoter filter. Lets a supreme admin scope the list to a single
                 promoter's orders (and a regular admin filter among their visible
                 promoters). Auto-submits on change to mirror the status dropdown. --}}
            <x-ui.select
                name="requested_by"
                autosubmit
                :placeholder="__('admin_orders.filters.all_promoters_option')"
            >
                @foreach($filterableUsers as $filterableUser)
                    @php
                        $roleTranslation = __('admin_orders.filters.role_' . $filterableUser->role, [], app()->getLocale());
                    @endphp
                    <option value="{{ $filterableUser->id }}" {{ request('requested_by') == $filterableUser->id ? 'selected' : '' }}>
                        {{ $filterableUser->name }} ({{ $roleTranslation !== 'admin_orders.filters.role_' . $filterableUser->role ? $roleTranslation : ucfirst($filterableUser->role) }})
                    </option>
                @endforeach
            </x-ui.select>

            <x-ui.input
                name="search"
                id="search_orders"
                :value="request('search')"
                leadingIcon="search"
                :placeholder="__('admin_orders.filters.search_placeholder')"
            />

            <x-ui.button type="submit" variant="primary" icon="search">
                {{ __('admin_orders.filters.search_button') }}
            </x-ui.button>
            <x-ui.button variant="secondary" :href="route('admin.orders.index')">
                {{ __('admin_orders.filters.clear_button') }}
            </x-ui.button>
        </x-ui.filter-form>

        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('admin_orders.table.header_id') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('admin_orders.table.header_customer') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden md:table-cell">{{ __('admin_orders.table.header_promoter') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden lg:table-cell">{{ __('admin_orders.table.header_date') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('admin_orders.table.header_items') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right">{{ __('admin_orders.table.header_total') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden sm:table-cell">{{ __('admin_orders.table.header_paid') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('admin_orders.table.header_commission') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('admin_orders.table.header_job_status') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('admin_orders.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($orders as $order)
                        @php
                            $jobStatusSlug = $order->job_status ?? 'unknown';
                            $statusText = __('admin_orders.statuses.' . $jobStatusSlug, [], app()->getLocale());
                            if ($statusText === 'admin_orders.statuses.' . $jobStatusSlug) { // Fallback if translation not found
                                $statusText = Illuminate\Support\Str::ucfirst($jobStatusSlug === 'unknown' ? __('admin_orders.statuses.unknown') : $jobStatusSlug);
                            }
                            $hasFailureReason = $jobStatusSlug === 'failed' && !empty($order->job_failure_reason);
                            $statusPillTitle = $hasFailureReason
                                ? __('admin_orders.table.status_tooltip_failure_prefix') . ' ' . Str::limit($order->job_failure_reason, 100)
                                : null;
                        @endphp
                        <x-ui.table-row
                            data-order-id="{{ $order->id }}"
                            data-job-status="{{ $jobStatusSlug }}"
                            data-order-number="{{ $order->order_number }}"
                        >
                            <x-ui.table-cell nowrap>
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100">#{{ $order->order_number }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap>
                                <div class="truncate w-32 sm:w-auto" title="{{ $order->email }}">{{ $order->email }}</div>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap class="hidden md:table-cell">
                                <div class="truncate w-28 sm:w-auto" title="{{ $order->requestedBy->name ?? __('admin_orders.table.promoter_not_available') }}">
                                    {{ $order->requestedBy->name ?? __('admin_orders.table.promoter_not_available') }}
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap class="hidden lg:table-cell">{{ $order->created_at->format('Y-m-d') }}</x-ui.table-cell>
                            <x-ui.table-cell>
                                @foreach($order->items as $item)
                                    {{ $item->quantity }}x {{ Str::limit($item->ticketType->name, 20) }}<br>
                                @endforeach
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap align="right" numeric>
                                {{ number_format($order->total, 2) }} <span class="text-zinc-400 text-xs">RSD</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap align="right" numeric class="hidden sm:table-cell">
                                {{ number_format($order->paid, 2) }} <span class="text-zinc-400 text-xs">RSD</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap align="right" numeric class="hidden md:table-cell">
                                {{ (in_array($order->job_status, ['completed', 'sent']) && isset($order->total_commission_earned)) ? number_format($order->total_commission_earned, 2) : __('admin_orders.table.commission_not_calculated') }} <span class="text-zinc-400 text-xs">RSD</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center" data-status-cell>
                                <x-ui.status-pill
                                    :status="$jobStatusSlug"
                                    :label="$statusText"
                                    :clickable="$hasFailureReason"
                                    :title="$statusPillTitle"
                                />
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                <div class="flex flex-col items-center gap-1">
                                    <x-ui.link variant="primary" size="sm" :href="route('admin.orders.show', $order->id)">
                                        {{ __('admin_orders.table.action_view') }}
                                    </x-ui.link>

                                    @if ($order->job_status === 'failed')
                                        <form action="{{ route('orders.rerunImageJob', $order->id) }}" method="POST" title="{{ __('admin_orders.table.action_generate_images_tooltip_failure_prefix') }} {{ Str::limit($order->job_failure_reason, 70) }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="warning" size="sm">
                                                {{ __('admin_orders.table.action_generate_images') }}
                                            </x-ui.button>
                                        </form>
                                    @endif

                                    @if (in_array($order->job_status, ['completed', 'sent', 'failed']))
                                        <form action="{{ route('orders.rerunEmailJob', $order->id) }}" method="POST" title="{{ __('admin_orders.table.action_send_mail_tooltip_base') }} {{ $order->job_status === 'failed' ? __('admin_orders.table.action_send_mail_tooltip_additional_failure_prefix') . ' ' . Str::limit($order->job_failure_reason, 70) : '' }}">
                                            @csrf
                                            <x-ui.button type="submit" variant="success" size="sm">
                                                {{ __('admin_orders.table.action_send_mail') }}
                                            </x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </x-ui.table-cell>
                        </x-ui.table-row>

                        {{-- Row for displaying the error message --}}
                        @if($hasFailureReason)
                            <x-ui.table-row :hover="false" id="error-row-{{ $order->id }}" class="bg-rose-50/70 dark:bg-rose-500/10" style="display: none;">
                                <x-ui.table-cell colspan="10">
                                    <div class="text-xs text-rose-700 dark:text-rose-200 py-2">
                                        <strong class="font-semibold block mb-1">{{ __('admin_orders.table.job_failure_reason_label') }}</strong>
                                        <pre class="whitespace-pre-wrap font-mono p-2 bg-rose-100 dark:bg-rose-700/40 dark:text-rose-100 rounded border border-rose-200 dark:border-rose-600">{{ $order->job_failure_reason }}</pre>
                                    </div>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endif
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="10">
                                <x-ui.empty-state
                                    icon="ticket"
                                    :title="__('admin_orders.table.no_orders_header')"
                                    :description="__('admin_orders.table.no_orders_message')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>

        @if ($orders->hasPages())
            <div class="mt-2">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    {{-- Live status updates via Laravel Echo + Reverb.
         `orderStatusBoard` subscribes to the private `orders` channel and
         patches the status cell of the row whose data-order-id matches the
         broadcast. If a new status arrives for an order not on this page
         (or filtered out), a tiny toast appears so admins notice it. --}}
    <script>
        function orderStatusBoard(jobStatusColors) {
            return {
                jobStatusColors: jobStatusColors || {},
                toast: null,
                toastTimer: null,
                init() {
                    if (!window.Echo) {
                        // Echo not loaded (e.g. bundle failed or this page
                        // doesn't include app.js). Stay silent rather than
                        // throwing in the browser console.
                        return;
                    }
                    window.Echo.private('orders')
                        .listen('.order.status.updated', (event) => this.handleStatusUpdate(event))
                        .error((err) => console.warn('[orderStatusBoard] Echo error:', err));
                },
                handleStatusUpdate(event) {
                    if (!event || !event.order_id) return;
                    const row = this.rootEl().querySelector('tr[data-order-id="' + event.order_id + '"]');
                    if (!row) {
                        this.showToast(event);
                        return;
                    }
                    const statusCell = row.querySelector('[data-status-cell]');
                    if (statusCell) {
                        statusCell.innerHTML = this.renderBadge(event.status, event.failure_reason);
                    }
                    row.setAttribute('data-job-status', event.status);
                    // Briefly highlight the row so the change is visually obvious.
                    row.classList.add('ring-2', 'ring-indigo-400', 'ring-offset-1', 'dark:ring-indigo-500');
                    setTimeout(() => {
                        row.classList.remove('ring-2', 'ring-indigo-400', 'ring-offset-1', 'dark:ring-indigo-500');
                    }, 2500);
                },
                renderBadge(status, failureReason) {
                    const colorClasses = (this.jobStatusColors[status] || this.jobStatusColors['N/A'] || 'bg-gray-100 text-gray-800');
                    const label = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown';
                    const hasFailure = status === 'failed' && failureReason;
                    const titleAttr = hasFailure
                        ? ' title="Failure: ' + this.escapeAttr(failureReason) + '"'
                        : '';
                    const chevron = hasFailure
                        ? '<svg class="ml-1 w-3 h-3 status-icon" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>'
                        : '';
                    return '<span class="px-2 py-1 inline-flex text-xs leading-tight font-semibold rounded-full ' + colorClasses + '"' + titleAttr + '>' + this.escapeHtml(label) + chevron + '</span>';
                },
                escapeHtml(s) {
                    return String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                },
                escapeAttr(s) {
                    return String(s ?? '').replace(/"/g, '&quot;').replace(/</g, '&lt;');
                },
                rootEl() {
                    return this.$root || document;
                },
                showToast(event) {
                    this.toast = event;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => { this.toast = null; }, 6000);
                },
                dismissToast() {
                    this.toast = null;
                    if (this.toastTimer) clearTimeout(this.toastTimer);
                },
            };
        }
        window.orderStatusBoard = orderStatusBoard;
    </script>

    {{-- Toast for off-page / filtered-out status updates. --}}
    <div
        x-show="toast"
        x-transition.opacity
        @click="dismissToast()"
        class="fixed bottom-6 right-6 z-50 max-w-sm cursor-pointer rounded-lg bg-indigo-600 px-4 py-3 text-white shadow-lg dark:bg-indigo-500"
        style="display: none;"
    >
        <template x-if="toast">
            <div>
                <p class="text-sm font-semibold">
                    {{ __('admin_orders.live.order_prefix') }}<span x-text="toast.order_id"></span>
                    —
                    <span x-text="toast.status"></span>
                </p>
                <p class="mt-1 text-xs opacity-90" x-show="toast.failure_reason" x-text="toast.failure_reason"></p>
                <p class="mt-1 text-[10px] opacity-75">{{ __('admin_orders.live.click_to_dismiss') }}</p>
            </div>
        </template>
    </div>
</x-layouts.app>