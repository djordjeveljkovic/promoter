<x-layouts.app :title="__('promoter_managers.sub_promoters.edit_form.page_title')">
    <div class="space-y-6">

        {{-- ===================== Page Header ===================== --}}
        <x-ui.page-header
            :eyebrow="__('promoter_managers.sub_promoters.page_title')"
            :title="__('promoter_managers.sub_promoters.edit_form.main_heading') . ' — ' . $sub->name"
        />

        {{-- ===================== Flash messages ===================== --}}
        @if(session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif

        {{-- ===================== Debt snapshot ===================== --}}
        <section>
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
        <x-ui.card>
            <x-ui.card.header
                :title="__('promoter_managers.sub_promoters.edit.record_payment_heading')"
                :subtitle="__('promoter_managers.sub_promoters.edit.record_payment_help')"
            />
            <form method="POST" action="{{ route('promoter_manager.payments.from_sub.store', $sub->id) }}" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-12 sm:p-6">
                @csrf
                <div class="sm:col-span-3">
                    <x-ui.field :label="__('promoter_managers.sub_promoters.edit.amount_label')" for="rec_amount" :error="$errors->first('amount')">
                        <x-ui.input id="rec_amount" name="amount" type="number" step="0.01" min="0.01"
                                    :max="max($debtSummary['amount_owed_to_manager'], 0) > 0 ? $debtSummary['amount_owed_to_manager'] : 9999999.99"
                                    placeholder="0.00" required />
                    </x-ui.field>
                </div>
                <div class="sm:col-span-3">
                    <x-ui.field :label="__('promoter_managers.sub_promoters.edit.paid_at_label')" for="rec_paid_at" :error="$errors->first('paid_at')">
                        <x-ui.input id="rec_paid_at" name="paid_at" type="date" :value="now()->toDateString()" />
                    </x-ui.field>
                </div>
                <div class="sm:col-span-6">
                    <x-ui.field :label="__('promoter_managers.sub_promoters.edit.note_label')" for="rec_note" :error="$errors->first('note')">
                        <x-ui.input id="rec_note" name="note" type="text" maxlength="500" />
                    </x-ui.field>
                </div>
                <div class="sm:col-span-12 flex items-center justify-end">
                    <x-ui.button type="submit" variant="primary" icon="banknotes">
                        {{ __('promoter_managers.sub_promoters.edit.submit_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- ===================== Account info / commission split ===================== --}}
        <form method="POST" action="{{ route('promoter_manager.sub_promoters.update', $sub->id) }}" class="space-y-6"
              x-data="{ modes: {} }">
            @csrf
            @method('PUT')

            <x-ui.card>
                <x-ui.card.header :title="__('promoter_managers.sub_promoters.edit_form.main_heading')" />
                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <x-ui.field :label="__('promoter_managers.sub_promoters.edit_form.name_label')" for="name" required>
                        <x-ui.input id="name" name="name" type="text" :value="old('name', $sub->name)" required />
                    </x-ui.field>

                    <x-ui.field :label="__('promoter_managers.sub_promoters.edit_form.email_label')" for="email" required>
                        <x-ui.input id="email" name="email" type="email" :value="old('email', $sub->email)" required />
                    </x-ui.field>
                </div>
                <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:px-6">
                    <x-ui.field
                        :label="__('promoter_managers.sub_promoters.edit_form.password_label')"
                        for="password"
                        :hint="__('promoter_managers.sub_promoters.edit_form.password_help')"
                    >
                        <x-ui.input id="password" name="password" type="password" minlength="8" />
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card.header
                    :title="__('promoter_managers.sub_promoters.edit_form.commission_split_heading')"
                    :subtitle="__('promoter_managers.sub_promoters.edit_form.commission_split_help')"
                />
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
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-900/40 rounded-lg space-y-2"
                             x-data='{ mode: @json($mode) }'
                             x-init='modes[{{ $type->id }}] = mode'>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $type->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ number_format($type->price, 2) }} RSD</div>
                                </div>
                                <input type="hidden" name="overrides[{{ $type->id }}][ticket_type_id]" value="{{ $type->id }}" />
                                <div class="inline-flex rounded-lg shadow-sm ring-1 ring-inset ring-zinc-300 dark:ring-zinc-700" role="group">
                                    <input type="hidden" name="overrides[{{ $type->id }}][commission_type]"
                                           :value="modes[{{ $type->id }}] ?? mode" />
                                    <button type="button"
                                            @click="modes[{{ $type->id }}] = 'percentage'"
                                            :class="(modes[{{ $type->id }}] ?? mode) === 'percentage'
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-800'"
                                            class="px-3 py-1.5 text-xs font-medium border border-r-0 rounded-l-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                        {{ __('promoter_managers.sub_promoters.edit_form.mode_percentage') }}
                                    </button>
                                    <button type="button"
                                            @click="modes[{{ $type->id }}] = 'fixed'"
                                            :class="(modes[{{ $type->id }}] ?? mode) === 'fixed'
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-800'"
                                            class="px-3 py-1.5 text-xs font-medium border rounded-r-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
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
                                       class="w-28 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">%</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('promoter_managers.sub_promoters.edit_form.percentage_help') }}</span>
                            </div>

                            <div class="flex items-center gap-2"
                                 x-show="(modes[{{ $type->id }}] ?? mode) === 'fixed'"
                                 x-cloak>
                                <input type="number" min="0" step="0.01"
                                       name="overrides[{{ $type->id }}][fixed_commission_amount]"
                                       value="{{ $fixValue }}"
                                       placeholder="0.00"
                                       class="w-32 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">RSD {{ __('promoter_managers.sub_promoters.edit_form.per_ticket_suffix') }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('promoter_managers.sub_promoters.edit_form.fixed_help') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('overrides') <p class="mt-1 px-5 text-xs text-rose-600 dark:text-rose-400 sm:px-6">{{ $message }}</p> @enderror
            </x-ui.card>

            <div class="flex items-center justify-end space-x-3">
                <x-ui.button variant="secondary" :href="route('promoter_manager.sub_promoters.index')">
                    {{ __('promoter_managers.sub_promoters.edit_form.cancel_button') }}
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    {{ __('promoter_managers.sub_promoters.edit_form.update_button') }}
                </x-ui.button>
            </div>
        </form>

        {{-- ===================== Payment history ===================== --}}
        <x-ui.card>
            <x-ui.card.header :title="__('promoter_managers.sub_promoters.edit.payment_history_heading')" />
            @if($recentPayments->isEmpty())
                <x-ui.empty-state
                    icon="banknotes"
                    :title="__('promoter_managers.sub_promoters.edit.payment_history_empty')"
                />
            @else
                <x-ui.table>
                    <x-ui.table-header>
                        <x-ui.table-row>
                            <x-ui.table-cell header>{{ __('promoter_managers.dashboard.payment_history.date') }}</x-ui.table-cell>
                            <x-ui.table-cell header>{{ __('promoter_managers.dashboard.payment_history.from_label') }}</x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>{{ __('promoter_managers.dashboard.payment_history.amount') }}</x-ui.table-cell>
                            <x-ui.table-cell header>{{ __('promoter_managers.dashboard.payment_history.note') }}</x-ui.table-cell>
                            <x-ui.table-cell header align="center">{{ __('promoter_managers.sub_promoters.edit_payment.table_actions') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @foreach($recentPayments as $payment)
                            <x-ui.table-row>
                                <x-ui.table-cell nowrap>
                                    <span class="text-zinc-900 dark:text-white">{{ $payment->paid_at->format('d M Y') }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell nowrap>
                                    <span class="text-zinc-700 dark:text-zinc-200">{{ $payment->payer?->name ?? '—' }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                        + {{ number_format((float) $payment->amount, 2) }} RSD
                                    </span>
                                </x-ui.table-cell>
                                <x-ui.table-cell>
                                    <span class="text-zinc-500 dark:text-zinc-400">{{ $payment->note ?? '—' }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell align="center">
                                    <div class="inline-flex items-center gap-2">
                                        <x-ui.link
                                            :href="route('promoter_manager.payments.from_sub.edit', $payment->id)"
                                            icon="pencil-square"
                                            size="sm"
                                        >
                                            {{ __('promoter_managers.sub_promoters.edit_payment.edit_action') }}
                                        </x-ui.link>
                                        <form
                                            method="POST"
                                            action="{{ route('promoter_manager.payments.from_sub.destroy', $payment->id) }}"
                                            class="inline"
                                            onsubmit="return confirm('{{ __('promoter_managers.sub_promoters.edit_payment.row_delete_confirm', ['amount' => number_format((float) $payment->amount, 2)]) }}')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-300">
                                                <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                {{ __('promoter_managers.sub_promoters.edit_payment.delete_action') }}
                                            </button>
                                        </form>
                                    </div>
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
</content>
</invoke>