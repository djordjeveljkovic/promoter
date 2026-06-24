<x-layouts.app :title="__('promoter_managers.sub_promoters.create_form.page_title')">
    <div class="space-y-6 max-w-3xl">

        <x-ui.page-header :title="__('promoter_managers.sub_promoters.create_form.main_heading')">
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('promoter_manager.sub_promoters.index')">
                    {{ __('promoter_managers.sub_promoters.create_form.cancel_button') }}
                </x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        <form method="POST" action="{{ route('promoter_manager.sub_promoters.store') }}" class="space-y-6"
              x-data="{ modes: {} }">
            @csrf

            <x-ui.alert variant="info">
                {{ __('promoter_managers.sub_promoters.create_form.commission_note') }}
            </x-ui.alert>

            @if ($errors->any())
                <x-ui.alert variant="danger">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui.alert>
            @endif

            <x-ui.card>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-ui.field :label="__('promoter_managers.sub_promoters.create_form.name_label')" for="name" required>
                        <x-ui.input id="name" name="name" type="text" :value="old('name', '')" required />
                    </x-ui.field>

                    <x-ui.field :label="__('promoter_managers.sub_promoters.create_form.email_label')" for="email" required>
                        <x-ui.input id="email" name="email" type="email" :value="old('email', '')" required />
                    </x-ui.field>
                </div>

                <div class="mt-6">
                    <x-ui.field :label="__('promoter_managers.sub_promoters.create_form.password_label')" for="password" required>
                        <x-ui.input id="password" name="password" type="password" required minlength="8" />
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card>
                <x-ui.card.header
                    :title="__('promoter_managers.sub_promoters.create_form.commission_split_heading')"
                    :subtitle="__('promoter_managers.sub_promoters.create_form.commission_split_help')"
                />

                <div class="p-5 sm:p-6 space-y-3">
                    @foreach($ticketTypes as $type)
                        @php
                            $oldType = old('overrides.' . $type->id . '.commission_type');
                            $oldPct  = old('overrides.' . $type->id . '.commission_percentage');
                            $oldFix  = old('overrides.' . $type->id . '.fixed_commission_amount');
                            $mode = $oldType !== null ? $oldType : 'percentage';
                            $pctValue = $oldPct !== null ? $oldPct : 100;
                            $fixValue = $oldFix !== null ? $oldFix : '';
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
                                        {{ __('promoter_managers.sub_promoters.create_form.mode_percentage') }}
                                    </button>
                                    <button type="button"
                                            @click="modes[{{ $type->id }}] = 'fixed'"
                                            :class="(modes[{{ $type->id }}] ?? mode) === 'fixed'
                                                ? 'bg-indigo-600 text-white border-indigo-600'
                                                : 'bg-white text-zinc-700 border-zinc-300 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-200 dark:border-zinc-700 dark:hover:bg-zinc-800'"
                                            class="px-3 py-1.5 text-xs font-medium border rounded-r-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                        {{ __('promoter_managers.sub_promoters.create_form.mode_fixed') }}
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
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('promoter_managers.sub_promoters.create_form.percentage_help') }}</span>
                            </div>

                            <div class="flex items-center gap-2"
                                 x-show="(modes[{{ $type->id }}] ?? mode) === 'fixed'"
                                 x-cloak>
                                <input type="number" min="0" step="0.01"
                                       name="overrides[{{ $type->id }}][fixed_commission_amount]"
                                       value="{{ $fixValue }}"
                                       placeholder="0.00"
                                       class="w-32 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white sm:text-sm" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">RSD {{ __('promoter_managers.sub_promoters.create_form.per_ticket_suffix') }}</span>
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('promoter_managers.sub_promoters.create_form.fixed_help') }}</span>
                            </div>
                        </div>
                    @endforeach

                    @error('overrides') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end space-x-3 pt-4">
                <x-ui.button variant="secondary" :href="route('promoter_manager.sub_promoters.index')">
                    {{ __('promoter_managers.sub_promoters.create_form.cancel_button') }}
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    {{ __('promoter_managers.sub_promoters.create_form.create_button') }}
                </x-ui.button>
            </div>
        </form>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
</content>
</invoke>