<x-layouts.app :title="__('email_settings.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ __('email_settings.main_heading') }}</h1>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 p-4 text-sm text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- Section 1: Current email configuration (read-only from .env) --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
                {{ __('email_settings.current_config.heading') }}
            </h2>

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ __('email_settings.current_config.help_text') }}
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- From address --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.from_address_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white break-all">
                        {{ $config['from_address'] ?: '—' }}
                    </div>
                </div>

                {{-- From name --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.from_name_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white break-all">
                        {{ $config['from_name'] ?: '—' }}
                    </div>
                </div>

                {{-- Mailer --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.mailer_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                        {{ strtoupper($config['mailer'] ?: '—') }}
                    </div>
                </div>

                {{-- Host --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.host_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white break-all">
                        {{ $config['host'] ?: '—' }}
                    </div>
                </div>

                {{-- Port --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.port_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                        {{ $config['port'] ?: '—' }}
                    </div>
                </div>

                {{-- Username --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.username_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white break-all">
                        {{ $config['username'] ?: '—' }}
                    </div>
                </div>

                {{-- Password (masked) --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.password_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                        @if ($config['password_set'])
                            <span class="text-green-600 dark:text-green-400">●●●●●●●●</span>
                            <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                ({{ __('email_settings.current_config.password_set') }})
                            </span>
                        @else
                            <span class="text-red-600 dark:text-red-400">
                                {{ __('email_settings.current_config.password_missing') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Scheme / Encryption --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.scheme_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                        {{ strtoupper($config['scheme'] ?: '—') }}
                    </div>
                </div>

                {{-- Environment --}}
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.current_config.env_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                        {{ strtoupper($config['app_env'] ?: '—') }}
                    </div>
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                {{ __('email_settings.current_config.edit_in_env', ['path' => '.env']) }}
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- Section 1b: Default email template source                  --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                <div>
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">
                        {{ __('email_settings.default_source.heading') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('email_settings.default_source.help_text') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <a href="{{ route('admin.email-settings.default-source') }}"
                       class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                        {{ __('email_settings.default_source.view_button') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700 md:col-span-2">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.default_source.view_path_label') }}
                    </div>
                    <div class="mt-1 text-sm font-mono font-semibold text-gray-800 dark:text-white break-all">
                        {{ $defaultView }}
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-md p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.default_source.size_label') }}
                    </div>
                    <div class="mt-1 text-sm font-semibold text-gray-800 dark:text-white">
                        @if ($defaultViewExists)
                            {{ number_format($defaultViewSize / 1024, 2) }} KB
                            <span class="ml-2 text-xs text-green-600 dark:text-green-400">
                                {{ __('email_settings.default_source.exists') }}
                            </span>
                        @else
                            <span class="text-red-600 dark:text-red-400">
                                {{ __('email_settings.default_source.missing') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ============================================================ --}}
        {{-- Section 2: Active template indicator + new template form   --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
                {{ __('email_settings.add_template.heading') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ __('email_settings.add_template.help_text', ['default' => $defaultView]) }}
            </p>

            <form action="{{ route('admin.email-settings.templates.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.name_label') }}
                        </label>
                        <input type="text" name="name" id="name" required maxlength="255"
                               value="{{ old('name') }}"
                               placeholder="{{ __('email_settings.add_template.name_placeholder') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.subject_label') }}
                        </label>
                        <input type="text" name="subject" id="subject" required maxlength="255"
                               value="{{ old('subject') }}"
                               placeholder="{{ __('email_settings.add_template.subject_placeholder') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('email_settings.add_template.description_label') }}
                    </label>
                    <input type="text" name="description" id="description" maxlength="500"
                           value="{{ old('description') }}"
                           placeholder="{{ __('email_settings.add_template.description_placeholder') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Template type: view or raw HTML --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="view_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.view_name_label') }}
                        </label>
                        <input type="text" name="view_name" id="view_name" maxlength="255"
                               value="{{ old('view_name') }}"
                               placeholder="{{ __('email_settings.add_template.view_name_placeholder') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('email_settings.add_template.view_name_help', ['default' => $defaultView]) }}
                        </p>
                    </div>

                    <div class="flex items-start pt-6">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   {{ old('is_active') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ __('email_settings.add_template.activate_label') }}
                            </span>
                        </label>
                    </div>
                </div>

                {{-- HTML content (only used if view_name is empty) --}}
                <div>
                    <label for="html_content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('email_settings.add_template.html_content_label') }}
                    </label>
                    <textarea name="html_content" id="html_content" rows="6"
                              placeholder="{{ __('email_settings.add_template.html_content_placeholder') }}"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">{{ old('html_content') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.add_template.html_content_help') }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('email_settings.add_template.submit_button') }}
                    </button>
                </div>
            </form>
        </section>

        {{-- ============================================================ --}}
        {{-- Section 3: Existing templates                              --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
                {{ __('email_settings.existing_templates.heading') }}
            </h2>

            @if ($templates->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('email_settings.existing_templates.no_data') }}
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('email_settings.existing_templates.header_name') }}
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('email_settings.existing_templates.header_subject') }}
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('email_settings.existing_templates.header_source') }}
                                </th>
                                <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('email_settings.existing_templates.header_active') }}
                                </th>
                                <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                                    {{ __('email_settings.existing_templates.header_actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($templates as $tpl)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $tpl->name }}</div>
                                        @if ($tpl->description)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $tpl->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        {{ $tpl->subject }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        @if ($tpl->view_name)
                                            <span class="inline-block px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                                {{ __('email_settings.existing_templates.source_view') }}
                                            </span>
                                            <div class="text-xs mt-1 text-gray-500 dark:text-gray-400 break-all">{{ $tpl->view_name }}</div>
                                        @else
                                            <span class="inline-block px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200">
                                                {{ __('email_settings.existing_templates.source_html') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-center">
                                        @if ($tpl->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                {{ __('email_settings.existing_templates.active_badge') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ __('email_settings.existing_templates.inactive_badge') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-center">
                                        <div class="inline-flex flex-wrap items-center justify-center gap-2">
                                            <a href="{{ route('admin.email-settings.templates.edit', $tpl) }}"
                                               class="inline-flex items-center px-3 py-1 rounded-md bg-gray-700 dark:bg-gray-600 hover:bg-gray-800 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                {{ __('email_settings.existing_templates.edit_button') }}
                                            </a>

                                            <form action="{{ route('admin.email-settings.templates.duplicate', $tpl) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                    {{ __('email_settings.existing_templates.duplicate_button') }}
                                                </button>
                                            </form>

                                            @if (! $tpl->is_active)
                                                <form action="{{ route('admin.email-settings.templates.activate', $tpl) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                        {{ __('email_settings.existing_templates.activate_button') }}
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.email-settings.templates.destroy', $tpl) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('email_settings.existing_templates.delete_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                    {{ __('email_settings.existing_templates.delete_button') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
