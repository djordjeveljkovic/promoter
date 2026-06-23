<x-layouts.app :title="__('promoter_managers.dashboard.page_title')">
    @php
        // Helpers used by the view. Kept tiny so the template stays
        // readable.
        $fmt = fn (float $v) => number_format($v, 2);
        $oweAmount     = (float) $debtSummary['amount_owed_to_organizers'];
        $myGross       = (float) $debtSummary['manager_gross_sales'];
        $myCommission  = (float) $debtSummary['manager_commission'];
        $subsGross     = (float) $debtSummary['subs_gross_sales'];
        $subsCommission = (float) $debtSummary['sub_commissions'];
        $alreadyPaid   = (float) $debtSummary['amount_already_paid_to_organizers'];
        $personalGross  = (float) $personal['gross_sales'];
        $personalComm   = (float) $personal['commission'];
        $personalComm30 = (float) $personal['commission_last_30_days'];
    @endphp

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

            {{-- ===================== Header ===================== --}}
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
                       class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        <flux:icon name="users" class="size-4" />
                        {{ __('promoter_managers.dashboard.subs_section.empty_cta') }}
                    </a>
                    <a href="{{ route('promoter.orders.create') }}"
                       class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 dark:bg-zinc-900 dark:text-gray-200 dark:ring-zinc-700 dark:hover:bg-zinc-800">
                        <flux:icon name="ticket" class="size-4" />
                        {{ __('promoter_managers.dashboard.new_order_button') }}
                    </a>
                </div>
            </header>

            {{-- ===================== Section 1 · HERO · What I owe to organizers ===================== --}}
            <section class="mb-8 sm:mb-10">
                @if($oweAmount > 0)
                    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 via-rose-600 to-orange-600 p-6 text-white shadow-lg sm:p-8">
                @elseif($oweAmount < 0)
                    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600 p-6 text-white shadow-lg sm:p-8">
                @else
                    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 p-6 text-white shadow-lg sm:p-8">
                @endif
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-medium uppercase tracking-wider text-white/80">
                                {{ __('promoter_managers.dashboard.owe_hero.eyebrow') }}
                            </p>
                            @if($oweAmount > 0)
                                <p class="mt-2 text-5xl font-bold tracking-tight sm:text-6xl">
                                    {{ $fmt($oweAmount) }} <span class="text-2xl font-semibold text-white/80">RSD</span>
                                </p>
                                <p class="mt-2 text-sm font-medium text-white/90">
                                    {{ __('promoter_managers.dashboard.owe_hero.headline_negative') }}
                                </p>
                            @elseif($oweAmount < 0)
                                <p class="mt-2 text-5xl font-bold tracking-tight sm:text-6xl">
                                    {{ $fmt(abs($oweAmount)) }} <span class="text-2xl font-semibold text-white/80">RSD</span>
                                </p>
                                <p class="mt-2 text-sm font-medium text-white/90">
                                    {{ __('promoter_managers.dashboard.owe_hero.overpaid_label') }}
                                </p>
                            @else
                                <p class="mt-2 text-4xl font-bold tracking-tight sm:text-5xl">
                                    {{ __('promoter_managers.dashboard.owe_hero.headline_positive') }}
                                </p>
                                <p class="mt-2 text-sm font-medium text-white/90">0 RSD</p>
                            @endif
                        </div>

                        {{-- Formula breakdown --}}
                        <div class="w-full lg:max-w-md">
                            <p class="text-xs font-medium uppercase tracking-wider text-white/70">
                                {{ __('promoter_managers.dashboard.owe_hero.breakdown_eyebrow') }}
                            </p>
                            <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                <dt class="text-white/80">{{ __('promoter_managers.dashboard.owe_hero.breakdown_my_gross') }}</dt>
                                <dd class="text-right font-semibold tabular-nums">+ {{ $fmt($myGross) }}</dd>

                                <dt class="text-white/80">{{ __('promoter_managers.dashboard.owe_hero.breakdown_subs_gross') }}</dt>
                                <dd class="text-right font-semibold tabular-nums">+ {{ $fmt($subsGross) }}</dd>

                                <dt class="text-white/80">{{ __('promoter_managers.dashboard.owe_hero.breakdown_my_commission') }}</dt>
                                <dd class="text-right font-semibold tabular-nums">− {{ $fmt($myCommission) }}</dd>

                                <dt class="text-white/80">{{ __('promoter_managers.dashboard.owe_hero.breakdown_subs_commission') }}</dt>
                                <dd class="text-right font-semibold tabular-nums">− {{ $fmt($subsCommission) }}</dd>

                                <dt class="text-white/80">{{ __('promoter_managers.dashboard.owe_hero.breakdown_paid') }}</dt>
                                <dd class="text-right font-semibold tabular-nums">− {{ $fmt($alreadyPaid) }}</dd>

                                <dt class="border-t border-white/30 pt-2 text-sm font-semibold uppercase tracking-wider text-white">
                                    {{ __('promoter_managers.dashboard.owe_hero.eyebrow') }}
                                </dt>
                                <dd class="border-t border-white/30 pt-2 text-right text-lg font-bold tabular-nums">
                                    {{ $fmt($oweAmount) }} RSD
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== Section 2 · AT A GLANCE · My numbers + My team ===================== --}}
            <section class="mb-8 sm:mb-10">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('promoter_managers.dashboard.at_a_glance.heading') }}
                </h2>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- My numbers --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ __('promoter_managers.dashboard.at_a_glance.my_numbers') }}
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.dashboard.at_a_glance.my_numbers_help') }}
                                </p>
                            </div>
                            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                <flux:icon name="user" class="size-4" />
                            </span>
                        </div>
                        <dl class="divide-y divide-gray-200 dark:divide-zinc-800">
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.my_gross') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-gray-900 dark:text-white">{{ $fmt($personalGross) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.my_commission') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($personalComm) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.my_orders') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format((int) $personal['orders_count']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.my_tickets') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format((int) $personal['tickets_sold']) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 bg-gray-50 px-5 py-3 dark:bg-zinc-800/40 sm:px-6">
                                <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.at_a_glance.my_commission_30d') }}</dt>
                                <dd class="text-sm font-semibold tabular-nums text-indigo-600 dark:text-indigo-400">{{ $fmt($personalComm30) }} RSD</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- My team --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                    {{ __('promoter_managers.dashboard.at_a_glance.my_team') }}
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.dashboard.at_a_glance.my_team_help') }}
                                </p>
                            </div>
                            <span class="inline-flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                <flux:icon name="users" class="size-4" />
                            </span>
                        </div>
                        <dl class="divide-y divide-gray-200 dark:divide-zinc-800">
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.subs_gross') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-gray-900 dark:text-white">{{ $fmt($subsGross) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.subs_commission') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-violet-600 dark:text-violet-400">{{ $fmt($teamCommissionTotal) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.subs_count') }}</dt>
                                <dd class="text-base font-semibold tabular-nums text-gray-900 dark:text-white">{{ $subPromoters->count() }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 px-5 py-3.5 sm:px-6">
                                <dt class="text-sm text-gray-600 dark:text-gray-300">{{ __('promoter_managers.dashboard.at_a_glance.subs_owe_me') }}</dt>
                                <dd class="text-base font-semibold tabular-nums {{ $teamOwedToManager > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $fmt($teamOwedToManager) }} <span class="text-xs font-normal text-gray-500">RSD</span></dd>
                            </div>
                            <div class="flex items-center justify-between gap-3 bg-gray-50 px-5 py-3 dark:bg-zinc-800/40 sm:px-6">
                                <dt class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.at_a_glance.subs_paid') }}</dt>
                                <dd class="text-sm font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($teamAlreadyPaidToManager) }} RSD</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </section>

            {{-- ===================== Section 3 · MY SUB-PROMOTERS ===================== --}}
            <section class="mb-8 sm:mb-10">
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.subs_section.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.dashboard.subs_section.sub_heading') }}
                        </p>
                    </div>
                </div>

                @if($subPromoters->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-800">
                            <flux:icon name="users" class="size-6 text-gray-400 dark:text-gray-500" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.dashboard.subs_section.empty') }}
                        </h3>
                        <a href="{{ route('promoter_manager.sub_promoters.create') }}"
                           class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            <flux:icon name="plus" class="size-4" />
                            {{ __('promoter_managers.dashboard.subs_section.empty_cta') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach($subDebts as $row)
                            @php
                                $sub = $row['user'];
                                $owed = (float) $row['amount_owed_to_manager'];
                                $paid = (float) $row['amount_already_paid'];
                                $gross = (float) $row['gross_sales'];
                                $subComm = (float) $row['sub_commission'];
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
                                    <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}"
                                       title="{{ __('promoter_managers.dashboard.subs_section.edit_button') }}"
                                       class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white p-2 text-gray-700 transition hover:bg-gray-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-gray-200 dark:hover:bg-zinc-700">
                                        <flux:icon name="pencil-square" class="size-4" />
                                    </a>
                                </div>

                                <dl class="grid grid-cols-2 gap-3 px-5 py-4 text-sm">
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.subs_section.card_gross') }}</dt>
                                        <dd class="mt-0.5 font-semibold tabular-nums text-gray-900 dark:text-white">{{ $fmt($gross) }} <span class="text-xs text-gray-500">RSD</span></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.subs_section.card_sub_commission') }}</dt>
                                        <dd class="mt-0.5 font-semibold tabular-nums text-violet-600 dark:text-violet-400">{{ $fmt($subComm) }} <span class="text-xs text-gray-500">RSD</span></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.subs_section.card_paid') }}</dt>
                                        <dd class="mt-0.5 font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">{{ $fmt($paid) }} <span class="text-xs text-gray-500">RSD</span></dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('promoter_managers.dashboard.subs_section.card_owed') }}</dt>
                                        <dd class="mt-0.5 text-lg font-bold tabular-nums {{ $owed > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            @if($owed > 0)
                                                {{ $fmt($owed) }} <span class="text-xs text-gray-500">RSD</span>
                                            @elseif($owed < 0)
                                                <span class="text-sm">{{ __('promoter_managers.dashboard.subs_section.owe_negative') }} {{ $fmt(abs($owed)) }} RSD</span>
                                            @else
                                                <span class="text-sm">{{ __('promoter_managers.dashboard.subs_section.owe_zero') }} ✓</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                <form method="POST" action="{{ route('promoter_manager.payments.from_sub.store', $sub->id) }}" class="flex items-center gap-2 border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-zinc-800 dark:bg-zinc-900/50">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="manager_edit" />
                                    <input type="number" name="amount" step="0.01" min="0.01" max="{{ max($owed, 0) > 0 ? $owed : 9999999.99 }}" required
                                           placeholder="{{ __('promoter_managers.dashboard.subs_section.amount_placeholder') }}"
                                           class="block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2" />
                                    <button type="submit"
                                            class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500">
                                        <flux:icon name="plus-circle" class="size-4" />
                                        <span class="hidden sm:inline">{{ __('promoter_managers.dashboard.subs_section.record_payment_button') }}</span>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- ===================== Section 4 · RECENT PAYMENTS ===================== --}}
            <section class="mb-8 sm:mb-10">
                <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('promoter_managers.dashboard.recent_payments.heading') }}
                </h2>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- From sub-promoters --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('promoter_managers.dashboard.recent_payments.from_subs') }}
                            </h3>
                        </div>
                        @if($recentPaymentsFromSubs->isEmpty())
                            <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.recent_payments.from_subs_empty') }}
                            </div>
                        @else
                            <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
                                @foreach($recentPaymentsFromSubs as $payment)
                                    <li class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $payment->payer?->name ?? '—' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $payment->paid_at->format('d M Y') }}
                                                    · {{ __('promoter_managers.dashboard.recent_payments.recorded_by') }}: {{ $payment->recorder?->name ?? '—' }}
                                                    @if($payment->note)
                                                        · {{ $payment->note }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-sm font-semibold tabular-nums text-emerald-600 dark:text-emerald-400">
                                                + {{ $fmt((float) $payment->amount) }} RSD
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    {{-- To organizers (recorded by admin) --}}
                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                        <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('promoter_managers.dashboard.recent_payments.to_organizers') }}
                            </h3>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.pay_organizers_notice.body') }}
                            </p>
                        </div>
                        @if($recentPaymentsToOrganizers->isEmpty())
                            <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.dashboard.recent_payments.to_organizers_empty') }}
                            </div>
                        @else
                            <ul class="divide-y divide-gray-200 dark:divide-zinc-800">
                                @foreach($recentPaymentsToOrganizers as $payment)
                                    <li class="px-5 py-3 sm:px-6">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $payment->paid_at->format('d M Y') }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ __('promoter_managers.dashboard.recent_payments.recorded_by') }}: {{ $payment->recorder?->name ?? '—' }}
                                                    @if($payment->note)
                                                        · {{ $payment->note }}
                                                    @endif
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-sm font-semibold tabular-nums text-indigo-600 dark:text-indigo-400">
                                                − {{ $fmt((float) $payment->amount) }} RSD
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-layouts.app>
