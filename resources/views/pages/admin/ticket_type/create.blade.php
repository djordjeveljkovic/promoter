<x-layouts.app :title="__('ticket_types.create_form.page_title')">
    <div class="space-y-6 max-w-3xl">
        <x-ui.page-header :title="__('ticket_types.create_form.main_heading')">
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('ticket_type.index')" icon="arrow-left">
                    {{ __('ticket_types.create_form.cancel_button') }}
                </x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($errors->any())
            <x-ui.alert variant="danger" :title="__('ticket_types.create_form.errors_title')">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('ticket_type.store') }}" enctype="multipart/form-data" class="space-y-6 p-6" id="createTicketTypeForm">
                @csrf

                {{-- Name + Price side by side --}}
                <div class="grid grid-cols-1 gap-x-4 gap-y-5 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <x-ui.field label="{{ __('ticket_types.create_form.name_label') }}" for="name" :error="$errors->first('name')" required>
                            <x-ui.input id="name" name="name" :value="old('name')"
                                        placeholder="{{ __('ticket_types.create_form.name_placeholder') }}" required />
                        </x-ui.field>
                    </div>
                    <div>
                        <x-ui.field label="{{ __('ticket_types.create_form.price_label') }}" for="price" :error="$errors->first('price')" required>
                            <div class="relative">
                                <x-ui.input id="price" name="price" type="number"
                                            :value="old('price')"
                                            placeholder="{{ __('ticket_types.create_form.price_placeholder') }}"
                                            step="0.01" min="0"
                                            class="pl-7 pr-12"
                                            required />
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-zinc-500 dark:text-zinc-400 sm:text-sm" id="currency-usd">{{ __('ticket_types.create_form.price_currency_suffix') }}</span>
                                </div>
                            </div>
                        </x-ui.field>
                    </div>
                </div>

                {{-- Photo with live preview --}}
                <div x-data="{
                        previewUrl: null,
                        previewName: null,
                        previewSize: null,
                        handleFile(e) {
                            const file = e.target.files[0];
                            if (!file) {
                                this.previewUrl = null;
                                this.previewName = null;
                                this.previewSize = null;
                                return;
                            }
                            this.previewName = file.name;
                            this.previewSize = (file.size / 1024).toFixed(1) + ' KB';
                            const reader = new FileReader();
                            reader.onload = (ev) => { this.previewUrl = ev.target.result; };
                            reader.readAsDataURL(file);
                        }
                     }">
                    <x-ui.field label="{{ __('ticket_types.create_form.photo_label') }}" for="photo"
                                :error="$errors->first('photo')"
                                :hint="__('ticket_types.create_form.photo_help_text')">
                        <div x-show="previewUrl" x-cloak class="mb-3 flex items-start gap-2">
                            <div>
                                <span class="block text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ __('ticket_types.create_form.new_photo_label') }}</span>
                                <img :src="previewUrl" alt="Photo preview" class="mt-1 h-20 w-auto rounded-md object-cover ring-2 ring-emerald-500">
                            </div>
                            <button type="button" @click="previewUrl = null; previewName = null; previewSize = null; $refs.photoInput.value = ''"
                                    class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                                    title="{{ __('ticket_types.create_form.remove_preview') }}">
                                <x-ui.icon name="x-mark" class="h-4 w-4" />
                            </button>
                        </div>
                        <input type="file"
                               name="photo"
                               id="photo"
                               x-ref="photoInput"
                               @change="handleFile($event)"
                               accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
                               class="block w-full text-sm text-zinc-900 border border-zinc-300 rounded-lg cursor-pointer bg-white dark:text-zinc-400 focus:outline-none dark:bg-zinc-900 dark:border-zinc-700 dark:placeholder-zinc-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-l-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100 dark:file:bg-indigo-600 dark:file:text-indigo-50 dark:hover:file:bg-indigo-500" />
                        <div class="mt-1 flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <span>{{ __('ticket_types.create_form.photo_help_text') }}</span>
                            <span x-show="previewName" x-cloak class="text-emerald-600 dark:text-emerald-400">
                                <span x-text="previewName"></span> <span x-show="previewSize">(<span x-text="previewSize"></span>)</span>
                            </span>
                        </div>
                    </x-ui.field>
                </div>

                {{-- Two-column layout on lg+, stacked on mobile --}}
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {{-- LEFT COLUMN: QR Coordinates --}}
                    <div class="space-y-6 lg:col-span-2">
                        {{-- QR Coordinates --}}
                        <fieldset class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            <legend class="px-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('ticket_types.create_form.qr_fieldset_legend') }} <span class="text-rose-500">*</span></legend>
                            <p class="mb-3 px-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('ticket_types.create_form.qr_help_text') }}</p>
                            <div class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                                <x-ui.field label="{{ __('ticket_types.create_form.qr_x_label') }}" for="qr_coordinate_x" :error="$errors->first('qr_coordinate_x')" required>
                                    <x-ui.input id="qr_coordinate_x" name="qr_coordinate_x" type="number"
                                                :value="old('qr_coordinate_x', old('qr_coordinates.x'))"
                                                placeholder="{{ __('ticket_types.create_form.qr_x_placeholder') }}"
                                                class="qr-input"
                                                min="0" required />
                                </x-ui.field>
                                <x-ui.field label="{{ __('ticket_types.create_form.qr_y_label') }}" for="qr_coordinate_y" :error="$errors->first('qr_coordinate_y')" required>
                                    <x-ui.input id="qr_coordinate_y" name="qr_coordinate_y" type="number"
                                                :value="old('qr_coordinate_y', old('qr_coordinates.y'))"
                                                placeholder="{{ __('ticket_types.create_form.qr_y_placeholder') }}"
                                                class="qr-input"
                                                min="0" required />
                                </x-ui.field>
                                <x-ui.field label="{{ __('ticket_types.create_form.qr_size_label') }}" for="qr_coordinate_size" :error="$errors->first('qr_coordinate_size')" required>
                                    <x-ui.input id="qr_coordinate_size" name="qr_coordinate_size" type="number"
                                                :value="old('qr_coordinate_size', old('qr_coordinates.size'))"
                                                placeholder="{{ __('ticket_types.create_form.qr_size_placeholder') }}"
                                                class="qr-input"
                                                min="10" required />
                                </x-ui.field>
                            </div>
                            <input type="hidden" name="qr_coordinates" id="qr_coordinates_json" value="{{ old('qr_coordinates', '{"x":0,"y":0,"size":100}') }}">
                            @error('qr_coordinates')
                                <p class="mt-2 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </fieldset>
                    </div>

                    {{-- RIGHT COLUMN: Commission tiers in a sticky scrollable card --}}
                    <div class="lg:col-span-1">
                        <div class="lg:sticky lg:top-4 flex max-h-[80vh] flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900/60">
                            <div class="flex items-start justify-between gap-2 border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/80">
                                <div>
                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                        {{ __('ticket_types.create_form.commissions_fieldset_legend') }} <span class="text-rose-500">*</span>
                                    </h3>
                                    <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('ticket_types.create_form.commissions_card_helper') }}
                                    </p>
                                </div>
                                <button type="button" id="add-commission-tier-btn"
                                        class="inline-flex shrink-0 items-center rounded-md border border-dashed border-zinc-400 dark:border-zinc-500 bg-white dark:bg-zinc-900 px-2.5 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-300 shadow-sm hover:bg-zinc-50 dark:hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    {{ __('ticket_types.create_form.commissions_add_tier_button') }}
                                </button>
                            </div>

                            <div id="commission-tiers-container" class="flex-1 space-y-4 overflow-y-auto p-4">
                                @php
                                    $oldCommissions = old('commissions', [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '']]);
                                    if (empty($oldCommissions)) {
                                        $oldCommissions = [['min_sold' => '', 'max_sold' => '', 'commission_amount' => '']];
                                    }
                                @endphp
                                @foreach($oldCommissions as $index => $commission)
                                    @php($minErr = "commissions.{$index}.min_sold")
                                    @php($maxErr = "commissions.{$index}.max_sold")
                                    @php($amtErr = "commissions.{$index}.commission_amount")
                                    <div class="commission-tier-row rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900/40">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                                {{ __('ticket_types.create_form.commissions_tier_label', ['index' => $index + 1]) }}
                                            </span>
                                            @if($index > 0 || count($oldCommissions) > 1)
                                                <button type="button" class="remove-commission-tier-btn inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs font-medium text-rose-600 hover:bg-rose-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                                        title="{{ __('ticket_types.create_form.commissions_remove_button') }}">
                                                    <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                                </button>
                                            @endif
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <x-ui.field label="{{ __('ticket_types.create_form.commissions_min_sold_label') }}" for="commissions_{{ $index }}_min_sold" :error="$errors->first($minErr)" required>
                                                <x-ui.input type="number" name="commissions[{{ $index }}][min_sold]" id="commissions_{{ $index }}_min_sold"
                                                            :value="$commission['min_sold'] ?? ''"
                                                            placeholder="{{ __('ticket_types.create_form.commissions_min_sold_placeholder') }}"
                                                            min="0" required />
                                            </x-ui.field>
                                            <x-ui.field label="{{ __('ticket_types.create_form.commissions_max_sold_label') }}" for="commissions_{{ $index }}_max_sold" :error="$errors->first($maxErr)">
                                                <x-ui.input type="number" name="commissions[{{ $index }}][max_sold]" id="commissions_{{ $index }}_max_sold"
                                                            :value="$commission['max_sold'] ?? ''"
                                                            placeholder="{{ __('ticket_types.create_form.commissions_max_sold_placeholder') }}"
                                                            min="0" />
                                            </x-ui.field>
                                        </div>
                                        <div class="mt-3">
                                            <x-ui.field label="{{ __('ticket_types.create_form.commissions_amount_label') }}" for="commissions_{{ $index }}_commission_amount" :error="$errors->first($amtErr)" required>
                                                <x-ui.input type="number" name="commissions[{{ $index }}][commission_amount]" id="commissions_{{ $index }}_commission_amount"
                                                            :value="$commission['commission_amount'] ?? ''"
                                                            placeholder="{{ __('ticket_types.create_form.commissions_amount_placeholder') }}"
                                                            step="0.01" min="0" required />
                                            </x-ui.field>
                                        </div>
                                    </div>
                                @endforeach
                                @error('commissions')
                                    <p class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-ui.button variant="secondary" :href="route('ticket_type.index')">
                        {{ __('ticket_types.create_form.cancel_button') }}
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        {{ __('ticket_types.create_form.create_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

<script>
// Pass translated strings to JavaScript
const translatedStrings = {
    minSoldLabel: "{{ __('ticket_types.create_form.commissions_min_sold_label') }}",
    minSoldPlaceholder: "{{ __('ticket_types.create_form.commissions_min_sold_placeholder') }}",
    maxSoldLabel: "{{ __('ticket_types.create_form.commissions_max_sold_label') }}",
    maxSoldPlaceholder: "{{ __('ticket_types.create_form.commissions_max_sold_placeholder') }}",
    commissionAmountLabel: "{{ __('ticket_types.create_form.commissions_amount_label') }}",
    commissionAmountPlaceholder: "{{ __('ticket_types.create_form.commissions_amount_placeholder') }}",
    removeButtonText: "{{ __('ticket_types.create_form.commissions_remove_button') }}"
};

document.addEventListener('DOMContentLoaded', function () {
    // --- QR Coordinates JSON updater ---
    const qrXInput = document.getElementById('qr_coordinate_x');
    const qrYInput = document.getElementById('qr_coordinate_y');
    const qrSizeInput = document.getElementById('qr_coordinate_size');
    const qrJsonInput = document.getElementById('qr_coordinates_json');

    function updateQrJson() {
        if (!qrXInput || !qrYInput || !qrSizeInput || !qrJsonInput) return;
        const qrData = {
            x: parseInt(qrXInput.value) || 0,
            y: parseInt(qrYInput.value) || 0,
            size: parseInt(qrSizeInput.value) || 100
        };
        qrJsonInput.value = JSON.stringify(qrData);
    }

    [qrXInput, qrYInput, qrSizeInput].forEach(input => {
        if (input) {
            input.addEventListener('input', updateQrJson);
        }
    });

    if (qrXInput && qrYInput && qrSizeInput && qrJsonInput) {
        let initialQrData = { x: 0, y: 0, size: 100 };
        try {
            const existingJson = JSON.parse(qrJsonInput.value);
            if (existingJson && typeof existingJson === 'object') {
                initialQrData = { ...initialQrData, ...existingJson };
            }
        } catch (e) {
            console.warn('Could not parse initial QR JSON data for individual fields:', qrJsonInput.value);
        }

        if (qrXInput.value === '' && (typeof initialQrData.x !== 'undefined')) qrXInput.value = initialQrData.x;
        if (qrYInput.value === '' && (typeof initialQrData.y !== 'undefined')) qrYInput.value = initialQrData.y;
        if (qrSizeInput.value === '' && (typeof initialQrData.size !== 'undefined')) qrSizeInput.value = initialQrData.size;

        updateQrJson();
    }

    // --- Dynamic Commission Tiers ---
    const commissionContainer = document.getElementById('commission-tiers-container');
    const addTierButton = document.getElementById('add-commission-tier-btn');

    let commissionTierIndex = commissionContainer ? commissionContainer.querySelectorAll('.commission-tier-row').length : 0;

    function addRemoveListener(button) {
        button.addEventListener('click', function() {
            this.closest('.commission-tier-row').remove();
            if (commissionContainer && commissionContainer.children.length === 0) {
                addCommissionTierHtml(0);
                commissionTierIndex = 1;
            }
        });
    }

    if (commissionContainer) {
        commissionContainer.querySelectorAll('.remove-commission-tier-btn').forEach(addRemoveListener);
    }

    if (commissionContainer && addTierButton && commissionContainer.children.length === 0) {
        addCommissionTierHtml(0);
        commissionTierIndex = 1;
    }

    if (addTierButton && commissionContainer) {
        addTierButton.addEventListener('click', function () {
            addCommissionTierHtml(commissionTierIndex);
            commissionTierIndex++;
        });
    }

    function addCommissionTierHtml(index) {
        const ts = typeof translatedStrings !== 'undefined' ? translatedStrings : {
            minSoldLabel: "Min Sold",
            minSoldPlaceholder: "e.g., 1",
            maxSoldLabel: "Max Sold",
            maxSoldPlaceholder: "e.g., 10 (empty for no limit)",
            commissionAmountLabel: "Commission Amount",
            commissionAmountPlaceholder: "e.g., 1.50",
            removeButtonText: "Remove",
            tierLabel: "Tier #"
        };

        const tierHtml = `
            <div class="commission-tier-row rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700 dark:bg-zinc-900/40">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">${ts.tierLabel}${index + 1}</span>
                    <button type="button" class="remove-commission-tier-btn inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs font-medium text-rose-600 hover:bg-rose-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10"
                            title="${ts.removeButtonText}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="commissions_${index}_min_sold" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">${ts.minSoldLabel} <span class="text-rose-500">*</span></label>
                        <input type="number" name="commissions[${index}][min_sold]" id="commissions_${index}_min_sold" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="${ts.minSoldPlaceholder}" min="0" required>
                    </div>
                    <div>
                        <label for="commissions_${index}_max_sold" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">${ts.maxSoldLabel}</label>
                        <input type="number" name="commissions[${index}][max_sold]" id="commissions_${index}_max_sold" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="${ts.maxSoldPlaceholder}" min="0">
                    </div>
                </div>
                <div class="mt-3">
                    <label for="commissions_${index}_commission_amount" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">${ts.commissionAmountLabel} <span class="text-rose-500">*</span></label>
                    <input type="number" name="commissions[${index}][commission_amount]" id="commissions_${index}_commission_amount" class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100" placeholder="${ts.commissionAmountPlaceholder}" step="0.01" min="0" required>
                </div>
            </div>
        `;
        if (commissionContainer) {
            commissionContainer.insertAdjacentHTML('beforeend', tierHtml);
            const newRow = commissionContainer.lastElementChild;
            const removeButton = newRow.querySelector('.remove-commission-tier-btn');
            if (removeButton) {
                addRemoveListener(removeButton);
            }
        }
    }
});
</script>
</x-layouts.app>