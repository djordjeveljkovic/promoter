<x-layouts.app :title="__('email_settings.create.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-3xl">

        <div class="mb-6">
            <a href="{{ route('admin.email-settings.index', ['tab' => 'templates']) }}"
               class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                <flux:icon.arrow-left class="w-4 h-4 mr-1" />
                {{ __('email_settings.create.back_to_list') }}
            </a>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white mb-2">
            {{ __('email_settings.create.heading') }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            {{ __('email_settings.create.help_text') }}
        </p>

        @if ($errors->any())
            <div class="mb-6 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.email-settings.templates.store') }}" method="POST"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-5"
              x-data="{ sourceType: @js(old('source_type', 'view')) }">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('email_settings.create.name_label') }}
                    </label>
                    <input type="text" name="name" id="name" required maxlength="255"
                           value="{{ old('name') }}"
                           placeholder="{{ __('email_settings.create.name_placeholder') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('email_settings.create.subject_label') }}
                    </label>
                    <input type="text" name="subject" id="subject" required maxlength="255"
                           value="{{ old('subject') }}"
                           placeholder="{{ __('email_settings.create.subject_placeholder') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('email_settings.create.description_label') }}
                </label>
                <input type="text" name="description" id="description" maxlength="500"
                       value="{{ old('description') }}"
                       placeholder="{{ __('email_settings.create.description_placeholder') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Source type radio --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('email_settings.create.source_type_label') }}
                </label>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900/60">
                        <input type="radio" name="source_type" value="view" x-model="sourceType"
                               class="mt-0.5 border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.create.source_type_view') }}
                        </span>
                    </label>
                    <label class="flex items-start gap-3 p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900/60">
                        <input type="radio" name="source_type" value="html" x-model="sourceType"
                               class="mt-0.5 border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.create.source_type_html') }}
                        </span>
                    </label>
                </div>
            </div>

            {{-- Blade view path (visible when source_type=view) --}}
            <div x-show="sourceType === 'view'" x-cloak>
                <label for="view_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('email_settings.create.view_name_label') }}
                </label>
                <input type="text" name="view_name" id="view_name" maxlength="255"
                       value="{{ old('view_name', $defaultView) }}"
                       placeholder="{{ __('email_settings.create.view_name_placeholder') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {!! __('email_settings.edit.editor_blade_variables') !!}
                </p>
            </div>

            {{-- HTML content (visible when source_type=html) --}}
            <div x-show="sourceType === 'html'" x-cloak>
                <label for="html_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('email_settings.create.html_content_label') }}
                </label>
                <textarea name="html_content" id="html_content" rows="8"
                          placeholder="{{ __('email_settings.create.html_content_placeholder') }}"
                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('html_content') }}</textarea>
            </div>

            <label class="flex items-start gap-3 p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900/60">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       {{ old('is_active') ? 'checked' : '' }}>
                <span class="text-sm text-gray-700 dark:text-gray-300">
                    {{ __('email_settings.create.make_default_label') }}
                </span>
            </label>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.email-settings.index', ['tab' => 'templates']) }}"
                   class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                    {{ __('email_settings.create.cancel_button') }}
                </a>
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('email_settings.create.submit_button') }}
                </button>
            </div>
        </form>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</x-layouts.app>
