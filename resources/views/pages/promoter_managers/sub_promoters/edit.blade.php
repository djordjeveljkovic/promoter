<x-layouts.app :title="__('promoter_managers.sub_promoters.edit_form.page_title')">
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">

            {{-- ===================== Page Header ===================== --}}
            <header class="mb-8 flex flex-col gap-2">
                <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                    {{ __('promoter_managers.sub_promoters.page_title') }}
                </p>
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    {{ __('promoter_managers.sub_promoters.edit_form.main_heading') }} — {{ $sub->name }}
                </h1>
            </header>

            {{-- ===================== Flash messages ===================== --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-200">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-5" />
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- ===================== Debt snapshot ===================== --}}
            <section class="mb-6 sm:mb-8">
                <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-700 p-6 text-white shadow-lg">
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-100">
                        {{ __('promoter_managers.sub_promoters.edit.debt_summary_heading') }}
                    </p>
                    <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.sub_promoters.edit.gross_label') }}</p>
                            <p class="mt-1 text-lg font-semibold sm:text-xl">{{ number_format($debtSummary['gross_sales'], 2) }} <span class="text-xs">RSD</span></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.sub_promoters.edit.sub_commission_label') }}</p>
                            <p class="mt-1 text-lg font-semibold sm:text-xl">{{ number_format($debtSummary['sub_commission'], 2) }} <span class="text-xs">RSD</span></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.sub_promoters.edit.paid_label') }}</p>
                            <p class="mt-1 text-lg font-semibold sm:text-xl">{{ number_format($debtSummary['amount_already_paid'], 2) }} <span class="text-xs">RSD</span></p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wider text-indigo-100/80">{{ __('promoter_managers.sub_promoters.edit.owed_label') }}</p>
                            <p class="mt-1 text-lg font-bold sm:text-xl">
                                {{ number_format($debtSummary['amount_owed_to_manager'], 2) }} <span class="text-xs">RSD</span>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================== Record payment ===================== --}}
            <section class="mb-6 sm:mb-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.sub_promoters.edit.record_payment_heading') }}
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.sub_promoters.edit.record_payment_help') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('promoter_manager.payments.from_sub.store', $sub->id) }}" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-12 sm:p-6">
                        @csrf
                        <div class="sm:col-span-3">
                            <label for="rec_amount" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.sub_promoters.edit.amount_label') }}
                            </label>
                            <input type="number" name="amount" id="rec_amount" step="0.01" min="0.01" max="{{ max($debtSummary['amount_owed_to_manager'], 0) > 0 ? $debtSummary['amount_owed_to_manager'] : 9999999.99 }}" required
                                   placeholder="0.00"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('amount') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label for="rec_paid_at" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.sub_promoters.edit.paid_at_label') }}
                            </label>
                            <input type="date" name="paid_at" id="rec_paid_at" value="{{ now()->toDateString() }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('paid_at') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-6">
                            <label for="rec_note" class="block text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                {{ __('promoter_managers.sub_promoters.edit.note_label') }}
                            </label>
                            <input type="text" name="note" id="rec_note" maxlength="500"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white sm:text-sm p-2.5" />
                            @error('note') <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-12 flex items-center justify-end">
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
                                <flux:icon name="banknotes" class="size-4" />
                                {{ __('promoter_managers.sub_promoters.edit.submit_button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ===================== Account info / commission split ===================== --}}
            <form method="POST" action="{{ route('promoter_manager.sub_promoters.update', $sub->id) }}" class="space-y-6 mb-6 sm:mb-8"
                  x-data="{ modes: {} }">
                @csrf
                @method('PUT')

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.sub_promoters.edit_form.main_heading') }}
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.edit_form.name_label') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $sub->name) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.edit_form.email_label') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $sub->email) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
                            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="border-t border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.edit_form.password_label') }}</label>
                            <input type="password" name="password" id="password" minlength="8"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.edit_form.password_help') }}</p>
                            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white">
                            {{ __('promoter_managers.sub_promoters.edit_form.commission_split_heading') }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.sub_promoters.edit_form.commission_split_help') }}
                        </p>
                    </div>
                    <div class="p-5 sm:p-6 space-y-3">
                        @foreach($ticketTypes as $type)
                            @php
                                $stored = $overridesByType[$type->id] ?? null;
                                $oldType = old('overrides.' . $type->id . '.commission_type');
                                $oldPct  = old('overrides.' . $type->id . '.commission_percentage');
                                $oldFix  = old('overrides.' . $type->id . '.fixed_commission_amount');

                                if ($oldType !== null) {
                                    $mode = $oldType;
                                    $pctValue = $oldPct !== null ? $oldPct : 100;
                                    $fixValue = $oldFix !== null ? $oldFix : '';
                                } elseif ($stored !== null) {
                                    $mode = $stored['type'] ?? 'percentage';
                                    $pctValue = $stored['percentage'] !== null ? $stored['percentage'] : 100;
                                    $fixValue = $stored['fixed_amount'] !== null ? $stored['fixed_amount'] : '';
                                } else {
                                    $mode = 'percentage';
                                    $pctValue = 100;
                                    $fixValue = '';
                                }
                            @endphp
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/40 rounded-md space-y-2"
                                 x-data='{ mode: @json($mode) }'
                                 x-init='modes[{{ $type->id }}] = mode'>
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $type->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($type->price, 2) }} RSD</div>
                                    </div>
                                    <input type="hidden" name="overrides[{{ $type->id }}][ticket_type_id]" value="{{ $type->id }}" />
                                    <div class="inline-flex rounded-md shadow-sm" role="group">
                                        <input type="hidden" name="overrides[{{ $type->id }}][commission_type]"
                                               :value="modes[{{ $type->id }}] ?? mode" />
                                        <button type="button"
                                                @click="modes[{{ $type->id }}] = 'percentage'"
                                                :class="(modes[{{ $type->id }}] ?? mode) === 'percentage'
                                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600'"
                                                class="px-3 py-1.5 text-xs font-medium border rounded-l-md">
                                            {{ __('promoter_managers.sub_promoters.edit_form.mode_percentage') }}
                                        </button>
                                        <button type="button"
                                                @click="modes[{{ $type->id }}] = 'fixed'"
                                                :class="(modes[{{ $type->id }}] ?? mode) === 'fixed'
                                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600'"
                                                class="px-3 py-1.5 text-xs font-medium border-t border-b border-r rounded-r-md">
                                            {{ __('promoter_managers.sub_promoters.edit_form.mode_fixed') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2"
                                     x-show="(modes[{{ $type->id }}] ?? mode) === 'percentage'"
                                     x-cloak>
                                    <input type="number" min="0" max="100" step="0.01"
                                           name="overrides[{{ $type->id }}][commission_percentage]"
                                           value="{{ $pctValue }}"
                                           class="w-28 rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">%</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.edit_form.percentage_help') }}</span>
                                </div>

                                <div class="flex items-center gap-2"
                                     x-show="(modes[{{ $type->id }}] ?? mode) === 'fixed'"
                                     x-cloak>
                                    <input type="number" min="0" step="0.01"
                                           name="overrides[{{ $type->id }}][fixed_commission_amount]"
                                           value="{{ $fixValue }}"
                                           placeholder="0.00"
                                           class="w-32 rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2" />
                                    <span class="text-sm text-gray-600 dark:text-gray-400">RSD {{ __('promoter_managers.sub_promoters.edit_form.per_ticket_suffix') }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.edit_form.fixed_help') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('overrides') <p class="mt-1 px-5 text-xs text-red-500 sm:px-6">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('promoter_manager.sub_promoters.index') }}"
                       class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        {{ __('promoter_managers.sub_promoters.edit_form.cancel_button') }}
                    </a>
                    <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent bg-black px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800">
                        {{ __('promoter_managers.sub_promoters.edit_form.update_button') }}
                    </button>
                </div>
            </form>

            {{-- ===================== Payment history ===================== --}}
            <section class="mb-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                    <div class="border-b border-gray-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('promoter_managers.sub_promoters.edit.payment_history_heading') }}
                        </h2>
                    </div>
                    @if($recentPayments->isEmpty())
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('promoter_managers.sub_promoters.edit.payment_history_empty') }}
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                                <thead class="bg-gray-50 dark:bg-zinc-800/50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">{{ __('promoter_managers.dashboard.payment_history.date') }}</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">{{ __('promoter_managers.dashboard.payment_history.from_label') }}</th>
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">{{ __('promoter_managers.dashboard.payment_history.amount') }}</th>
                                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-300 sm:px-6">{{ __('promoter_managers.dashboard.payment_history.note') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                                    @foreach($recentPayments as $payment)
                                        <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-900 dark:text-white sm:px-6">
                                                {{ $payment->paid_at->format('d M Y') }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-700 dark:text-gray-200 sm:px-6">
                                                {{ $payment->payer?->name ?? '—' }}
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-emerald-600 dark:text-emerald-400 sm:px-6">
                                                + {{ number_format((float) $payment->amount, 2) }} RSD
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
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
