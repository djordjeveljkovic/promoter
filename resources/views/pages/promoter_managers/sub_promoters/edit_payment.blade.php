<x-layouts.app :title="__('promoter_managers.sub_promoters.edit_payment.page_title')">
    <div class="space-y-6">

        {{-- ===================== Page Header ===================== --}}
        <x-ui.page-header
            :eyebrow="__('promoter_managers.sub_promoters.page_title')"
            :title="__('promoter_managers.sub_promoters.edit_payment.main_heading') . ' — ' . $sub->name"
            :subtitle="__('promoter_managers.sub_promoters.edit_payment.sub_heading', [
                'date' => $payment->paid_at?->format('d M Y') ?? '—',
                'amount' => number_format((float) $payment->amount, 2),
            ])"
        >
            <x-slot:actions>
                <x-ui.button
                    variant="secondary"
                    :href="route('promoter_manager.sub_promoters.edit', $sub->id)"
                    icon="arrow-left"
                >
                    {{ __('promoter_managers.sub_promoters.edit_payment.back_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- ===================== Flash messages ===================== --}}
        @if(session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif

        {{-- ===================== Edit form ===================== --}}
        <x-ui.card>
            <x-ui.card.header
                :title="__('promoter_managers.sub_promoters.edit_payment.form_heading')"
            />

            <form method="POST"
                  action="{{ route('promoter_manager.payments.from_sub.update', $payment->id) }}"
                  class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-12 sm:p-6">
                @csrf
                @method('PUT')

                <div class="sm:col-span-3">
                    <x-ui.field
                        :label="__('promoter_managers.sub_promoters.edit.amount_label')"
                        for="rec_amount"
                        :error="$errors->first('amount')"
                    >
                        <x-ui.input
                            id="rec_amount"
                            name="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :value="old('amount', $payment->amount)"
                            required
                        />
                    </x-ui.field>
                </div>

                <div class="sm:col-span-3">
                    <x-ui.field
                        :label="__('promoter_managers.sub_promoters.edit.paid_at_label')"
                        for="rec_paid_at"
                        :error="$errors->first('paid_at')"
                    >
                        <x-ui.input
                            id="rec_paid_at"
                            name="paid_at"
                            type="date"
                            :value="old('paid_at', $payment->paid_at?->toDateString())"
                        />
                    </x-ui.field>
                </div>

                <div class="sm:col-span-6">
                    <x-ui.field
                        :label="__('promoter_managers.sub_promoters.edit.note_label')"
                        for="rec_note"
                        :error="$errors->first('note')"
                    >
                        <x-ui.input
                            id="rec_note"
                            name="note"
                            type="text"
                            maxlength="500"
                            :value="old('note', $payment->note)"
                        />
                    </x-ui.field>
                </div>

                <div class="sm:col-span-12 flex items-center justify-end gap-3">
                    <x-ui.button
                        variant="secondary"
                        :href="route('promoter_manager.sub_promoters.edit', $sub->id)"
                    >
                        {{ __('promoter_managers.sub_promoters.edit_payment.cancel_button') }}
                    </x-ui.button>
                    <x-ui.button type="submit" variant="primary" icon="check">
                        {{ __('promoter_managers.sub_promoters.edit_payment.save_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- ===================== Danger zone: delete the payment ===================== --}}
        <x-ui.card>
            <x-ui.card.header
                :title="__('promoter_managers.sub_promoters.edit_payment.danger_heading')"
                :subtitle="__('promoter_managers.sub_promoters.edit_payment.danger_help')"
            />
            <div class="p-5 sm:p-6">
                <form
                    method="POST"
                    action="{{ route('promoter_manager.payments.from_sub.destroy', $payment->id) }}"
                    onsubmit="return confirm('{{ __('promoter_managers.sub_promoters.edit_payment.delete_confirm') }}')"
                >
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger" icon="trash">
                        {{ __('promoter_managers.sub_promoters.edit_payment.delete_button') }}
                    </x-ui.button>
                </form>
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>