<x-layouts.app :title="__('promoter_managers.sub_promoters.edit_form.page_title')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('promoter_managers.sub_promoters.edit_form.main_heading') }}</h1>
    </div>

    <form method="POST" action="{{ route('promoter_manager.sub_promoters.update', $sub->id) }}" class="space-y-6 max-w-3xl"
          x-data="{ modes: {} }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.edit_form.password_label') }}</label>
            <input type="password" name="password" id="password" minlength="8"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.edit_form.password_help') }}</p>
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">{{ __('promoter_managers.sub_promoters.edit_form.commission_split_heading') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('promoter_managers.sub_promoters.edit_form.commission_split_help') }}</p>

            <div class="space-y-3">
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

                        {{-- Percentage input --}}
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

                        {{-- Fixed-amount input --}}
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
            @error('overrides') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4">
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

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-layouts.app>
