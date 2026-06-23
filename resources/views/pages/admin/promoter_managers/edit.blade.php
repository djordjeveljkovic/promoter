<x-layouts.app :title="__('promoter_managers.edit_form.page_title')">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('promoter_managers.edit_form.main_heading') }}</h1>
    </div>

    <form method="POST" action="{{ route('admin.promoter_managers.update', $manager->id) }}" class="space-y-6 max-w-3xl">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.edit_form.name_label') }}</label>
            <input type="text" name="name" id="name" value="{{ old('name', $manager->name) }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.edit_form.email_label') }}</label>
            <input type="email" name="email" id="email" value="{{ old('email', $manager->email) }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.edit_form.password_label') }}</label>
            <input type="password" name="password" id="password" minlength="8"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('promoter_managers.edit_form.password_help') }}</p>
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="paid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('promoter_managers.edit_form.paid_label') }}</label>
            <input type="number" step="0.01" min="0" name="paid" id="paid" value="{{ old('paid', $manager->paid ?? 0) }}"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm p-2.5" />
            @error('paid') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-end space-x-3 pt-4">
            <a href="{{ route('admin.promoter_managers.index') }}"
               class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                {{ __('promoter_managers.edit_form.cancel_button') }}
            </a>
            <button type="submit"
                    class="inline-flex justify-center rounded-md border border-transparent bg-black px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-zinc-800">
                {{ __('promoter_managers.edit_form.update_button') }}
            </button>
        </div>
    </form>
</x-layouts.app>
