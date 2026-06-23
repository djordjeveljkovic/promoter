<x-layouts.app :title="__('orders.show_page_title', ['orderNumber' => $order->order_number])">
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">

            {{-- ===================== Page Header ===================== --}}
            <header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ __('orders.show.eyebrow') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ __('orders.show.main_heading', ['orderNumber' => $order->order_number]) }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.sub_heading') }}
                    </p>
                </div>
                <a href="{{ route('promoter.orders.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                    {{ __('orders.show.back_to_orders') }}
                </a>
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
            @if (session('info'))
                <div class="mb-4 rounded-md bg-blue-50 p-4 text-sm text-blue-700 dark:bg-blue-700 dark:text-blue-100">
                    {{ session('info') }}
                </div>
            @endif

            {{-- ===================== Order Summary ===================== --}}
            <section class="mb-8 grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.summary.customer_label') }}
                    </span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white break-all">
                        {{ $order->email }}
                    </span>
                </div>
                <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.summary.placed_on_label') }}
                    </span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $order->created_at->format('M d, Y H:i') }}
                    </span>
                </div>
                <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.summary.total_label') }}
                    </span>
                    <span class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ number_format($totalPrice, 2) }} RSD
                    </span>
                </div>
                <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.summary.status_label') }}
                    </span>
                    <span>
                        @php
                            $statusKey = $order->job_status ?? 'unknown';
                            $statusText = __('orders.statuses.' . $statusKey, [], App::getLocale());
                            if ($statusText === 'orders.statuses.' . $statusKey) {
                                $statusText = \Illuminate\Support\Str::ucfirst($order->job_status ?? __('orders.statuses.unknown'));
                            }
                            $statusClass = $jobStatusColors[$order->job_status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100';
                        @endphp
                        <span class="inline-flex lowercase items-center px-2 text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </span>
                </div>
            </section>

            {{-- ===================== Seller / Commission Breakdown ===================== --}}
            <section class="mb-8 grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-gray-200 ring-1 ring-gray-200 dark:bg-zinc-800 dark:ring-zinc-800 sm:grid-cols-2">
                <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.summary.seller_label') }}
                    </span>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">
                        @if($order->requested_by === Auth::id())
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                {{ __('orders.seller_self_badge') }}
                            </span>
                            &middot; {{ $order->requestedBy->name ?? __('orders.seller_unknown') }}
                        @else
                            {{ $order->requestedBy->name ?? __('orders.seller_unknown') }}
                        @endif
                    </span>
                </div>
                <div class="flex flex-col gap-1 bg-white p-5 dark:bg-zinc-900 sm:p-6">
                    <span class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        {{ __('orders.show.summary.my_commission_label') }}
                    </span>
                    @php
                        $myCommission = $commissionByOrder[$order->id] ?? 0.0;
                    @endphp
                    <span class="text-2xl font-bold tracking-tight text-emerald-700 dark:text-emerald-400">
                        {{ number_format($myCommission, 2) }} RSD
                    </span>
                    @if($order->requested_by !== Auth::id() && $order->total_commission_earned > $myCommission)
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('orders.show.summary.commission_split_note', ['total' => number_format($order->total_commission_earned, 2)]) }}
                        </span>
                    @endif
                </div>
            </section>

            {{-- ===================== Items ===================== --}}
            <section class="mb-8">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('orders.show.items.heading') }}
                </h2>
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('orders.show.items.header_type') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('orders.show.items.header_quantity') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('orders.show.items.header_unit_price') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('orders.show.items.header_subtotal') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $item->ticketType->name ?? __('orders.show.items.unknown_type') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-300">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500 dark:text-gray-300">
                                        {{ number_format($item->ticketType->price ?? 0, 2) }} RSD
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($item->quantity * ($item->ticketType->price ?? 0), 2) }} RSD
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- ===================== Tickets / QR Codes ===================== --}}
            <section class="mb-8">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('orders.show.tickets.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('orders.show.tickets.sub_heading', ['count' => $order->tickets->count()]) }}
                        </p>
                    </div>
                    @if($order->tickets->isNotEmpty())
                        <a href="{{ route('admin.orders.downloadQRCodes', ['order' => $order->id]) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-600 dark:bg-slate-600 dark:hover:bg-slate-500">
                            {{ __('orders.show.tickets.download_all_button') }}
                        </a>
                    @endif
                </div>

                @if($order->tickets->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('orders.show.tickets.empty') }}
                        </p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach($order->tickets as $ticket)
                            @php
                                $hasQr = !empty($ticket->qr_code_path)
                                    && \Illuminate\Support\Facades\Storage::disk('public')->exists($ticket->qr_code_path);
                            @endphp
                            <div class="flex flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                                @if($hasQr)
                                    <div class="flex items-center justify-center bg-white p-4 dark:bg-white">
                                        <img src="{{ asset('storage/' . $ticket->qr_code_path) }}"
                                             alt="{{ __('orders.show.tickets.image_alt_prefix') }} {{ $ticket->id }}"
                                             class="h-40 w-40 object-contain">
                                    </div>
                                @else
                                    <div class="flex h-44 items-center justify-center bg-zinc-100 px-4 text-center dark:bg-zinc-800">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('orders.show.tickets.qr_not_available') }}
                                        </span>
                                    </div>
                                @endif
                                <div class="flex flex-col gap-1 p-4">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ __('orders.show.tickets.card_title_prefix') }}{{ $ticket->id }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 break-all">
                                        {{ $ticket->ticketType->name ?? __('orders.show.tickets.unknown_type') }}
                                    </span>
                                    <span class="mt-2">
                                        @if($ticket->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                {{ __('orders.show.tickets.status_active') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                                {{ __('orders.show.tickets.status_inactive') }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
