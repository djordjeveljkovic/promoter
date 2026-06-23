<x-layouts.app :title="__('promoter_managers.sub_promoters.page_title')">
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

            {{-- ===================== Page Header ===================== --}}
            <header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ __('promoter_managers.dashboard.eyebrow') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ __('promoter_managers.sub_promoters.main_heading') }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        {{ __('promoter_managers.sub_promoters.sub_heading') }}
                    </p>
                </div>
                <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                    <flux:icon name="plus" class="size-4" />
                    {{ __('promoter_managers.sub_promoters.add_button') }}
                </a>
            </header>

            {{-- ===================== Aggregated debt hero ===================== --}}
            <section class="mb-8 sm:mb-10">
                <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-violet-700 to-fuchsia-700 p-6 text-white shadow-lg sm:p-8">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-indigo-100">
                                {{ __('promoter_managers.dashboard.quick_stats.team_owed_to_me') }}
                            </p>
                            <p class="mt-1 text-sm text-indigo-100/90 max-w-xl">
                                {{ __('promoter_managers.dashboard.team_debts.sub_heading') }}
                            </p>
                            <div class="mt-4 flex flex-wrap items-baseline gap-3">
                                <span class="text-4xl font-bold tracking-tight sm:text-5xl">
                                    {{ number_format($totalOwed, 2) }}
                                </span>
                                <span class="text-lg font-medium text-indigo-100/90">RSD</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm sm:max-w-md">
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.sub_promoters.table.header_paid') }}</p>
                                <p class="mt-1 font-semibold">{{ number_format($totalAlreadyPaid, 2) }} RSD</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.dashboard.quick_stats.subs_count') }}</p>
                                <p class="mt-1 font-semibold">{{ $subs->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== Per-sub cards ===================== --}}
            @if($subs->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800">
                        <flux:icon name="users" class="size-6 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                        {{ __('promoter_managers.sub_promoters.table.empty_header') }}
                    </h3>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">
                        {{ __('promoter_managers.sub_promoters.table.empty_message') }}
                    </p>
                    <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                       class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        <flux:icon name="plus" class="size-4" />
                        {{ __('promoter_managers.sub_promoters.add_button') }}
                    </a>
                </div>
            @else
                <section class="mb-8">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($subs as $sub)
                            @php
                                $owed = $sub->amountOwedToManager ?? 0;
                                $paid = $sub->amountAlreadyPaidToManager ?? 0;
                                $gross = $sub->grossSales ?? 0;
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
                                        {{ $sub->totalOrders ?? 0 }} {{ __('promoter_managers.dashboard.my_subs.orders_unit') }}
                                    </span>
                                </div>
                                <div class="px-5 py-4">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.table.header_gross') }}</p>
                                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ number_format($gross, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.table.header_commission') }}</p>
                                            <p class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ number_format($sub->totalCommissionEarned, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.table.header_paid') }}</p>
                                            <p class="mt-0.5 font-semibold text-emerald-600 dark:text-emerald-400">{{ number_format($paid, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                        </div>
                                        <div>
                                            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.table.header_owed') }}</p>
                                            @if($owed > 0)
                                                <p class="mt-0.5 text-lg font-bold text-rose-600 dark:text-rose-400">{{ number_format($owed, 2) }} <span class="text-xs text-gray-500">RSD</span></p>
                                            @elseif($owed < 0)
                                                <p class="mt-0.5 text-sm font-semibold text-amber-600 dark:text-amber-400">
                                                    {{ __('promoter_managers.sub_promoters.table.owe_negative') }} {{ number_format(abs($owed), 2) }} RSD
                                                </p>
                                            @else
                                                <p class="mt-0.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                                    {{ __('promoter_managers.sub_promoters.table.owe_zero') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!empty($ticketTypes) && $ticketTypes->count())
                                        <div class="mt-4 border-t border-gray-200 pt-3 dark:border-zinc-800">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                                {{ __('promoter_managers.sub_promoters.table.commission_per_type_label') }}
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($ticketTypes as $type)
                                                    @php $ov = $sub->overridesByType[$type->id] ?? null; @endphp
                                                    <span class="px-2 py-0.5 rounded-md text-xs
                                                        {{ $ov === null
                                                            ? 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                                                            : 'bg-indigo-50 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-200' }}">
                                                        <span class="font-medium">{{ $type->name }}:</span>
                                                        @if($ov === null)
                                                            {{ __('promoter_managers.sub_promoters.table.no_override') }}
                                                        @elseif(($ov['type'] ?? 'percentage') === 'fixed')
                                                            {{ number_format((float) ($ov['fixed_amount'] ?? 0), 2) }} {{ __('promoter_managers.sub_promoters.table.per_ticket_suffix') }}
                                                        @else
                                                            {{ number_format((float) ($ov['percentage'] ?? 0), 2) }}%
                                                        @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    <form method="POST" action="{{ route('promoter_manager.payments.from_sub.store', $sub->id) }}" class="flex-1 grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto] sm:items-end">
                                        @csrf
                                        <input type="hidden" name="redirect_to" value="sub_promoters_index" />
                                        <div>
                                            <label for="liq_amt-{{ $sub->id }}" class="sr-only">{{ __('promoter_managers.sub_promoters.table.action_record_payment') }}</label>
                                            <input type="number" name="amount" id="liq_amt-{{ $sub->id }}" step="0.01" min="0.01" max="{{ max($owed, 0) > 0 ? $owed : 9999999.99 }}" required
                                                   placeholder="0.00"
                                                   class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2" />
                                        </div>
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                                            <flux:icon name="plus-circle" class="size-4" />
                                            <span>{{ __('promoter_managers.sub_promoters.table.action_record_payment') }}</span>
                                        </button>
                                    </form>
                                    <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}"
                                       class="inline-flex items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-200 dark:hover:bg-zinc-700">
                                        <flux:icon name="pencil-square" class="size-4" />
                                    </a>
                                    <form action="{{ route('promoter_manager.sub_promoters.destroy', $sub->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-1.5 rounded-md border border-rose-200 bg-white px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50 dark:border-rose-700/40 dark:bg-zinc-800 dark:text-rose-400 dark:hover:bg-rose-900/20"
                                                onclick="return confirm('{{ __('promoter_managers.sub_promoters.table.delete_confirm_message') }}')">
                                            <flux:icon name="trash" class="size-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
