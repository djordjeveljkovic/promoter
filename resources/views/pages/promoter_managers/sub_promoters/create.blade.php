<x-layouts.app :title="__('promoter_managers.sub_promoters.create_form.page_title')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('promoter_managers.sub_promoters.create_form.main_heading') }}</h1>
    </div>

    <form method="POST" action="{{ route('promoter_manager.sub_promoters.store') }}" class="space-y-6 max-w-3xl">
        @csrf

        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-md p-4 text-sm text-blue-800 dark:text-blue-200">
            {{ __('promoter_managers.sub_promoters.create_form.commission_note') }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.create_form.name_label') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', '') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
                @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.create_form.email_label') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', '') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
                @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.sub_promoters.create_form.password_label') }}</label>
            <input type="password" name="password" id="password" required minlength="8"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div x-data="{
                pct: @json(old('overrides', []))
            }">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">{{ __('promoter_managers.sub_promoters.create_form.commission_split_heading') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('promoter_managers.sub_promoters.create_form.commission_split_help') }}</p>

            <div class="space-y-3">
                @foreach($ticketTypes as $type)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-3 bg-gray-50 dark:bg-gray-700/40 rounded-md">
                        <div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $type->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($type->price, 2) }} RSD</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" min="0" max="100" step="0.01" name="overrides[{{ $type->id }}][commission_percentage]"
                                   value="{{ old('overrides.' . $type->id . '.commission_percentage', 100) }}"
                                   class="w-24 rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">%</span>
                            <input type="hidden" name="overrides[{{ $type->id }}][ticket_type_id]" value="{{ $type->id }}" />
                        </div>
                    </div>
                @endforeach
            </div>
            @error('overrides') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4">
            <a href="{{ route('promoter_manager.sub_promoters.index') }}"
               class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ __('promoter_managers.sub_promoters.create_form.cancel_button') }}
            </a>
            <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-black px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800">
                {{ __('promoter_managers.sub_promoters.create_form.create_button') }}
            </button>
        </div>
    </form>
</x-layouts.app>
