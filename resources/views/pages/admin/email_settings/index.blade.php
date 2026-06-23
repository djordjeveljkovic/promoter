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
        {{-- Section 1: Effective email configuration (read-only summary) --}}
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
        </section>

        {{-- ============================================================ --}}
        {{-- Section 2: Edit mail configuration (DB-backed)              --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
                {{ __('email_settings.edit_config.heading') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ __('email_settings.edit_config.help_text') }}
            </p>

            <form action="{{ route('admin.email-settings.mail-config.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Mailer --}}
                    <div>
                        <label for="mailer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.mailer_label') }}
                        </label>
                        <select name="mailer" id="mailer"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('email_settings.edit_config.encryption_none') }} (use .env)</option>
                            @foreach(['smtp','sendmail','log','array','failover','roundrobin'] as $m)
                                <option value="{{ $m }}" {{ old('mailer', $mailSettings->mailer) === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('email_settings.edit_config.mailer_help') }}
                        </p>
                    </div>

                    {{-- Host --}}
                    <div>
                        <label for="host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.host_label') }}
                        </label>
                        <input type="text" name="host" id="host" maxlength="255"
                               value="{{ old('host', $mailSettings->host) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Port --}}
                    <div>
                        <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.port_label') }}
                        </label>
                        <input type="number" name="port" id="port" min="1" max="65535"
                               value="{{ old('port', $mailSettings->port) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Username --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.username_label') }}
                        </label>
                        <input type="text" name="username" id="username" maxlength="255" autocomplete="off"
                               value="{{ old('username', $mailSettings->username) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.password_label') }}
                        </label>
                        <input type="password" name="password" id="password" maxlength="1024" autocomplete="new-password"
                               placeholder="{{ __('email_settings.edit_config.password_placeholder') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @if (!empty($mailSettings->password_encrypted))
                            <label class="mt-2 inline-flex items-center">
                                <input type="hidden" name="clear_password" value="0">
                                <input type="checkbox" name="clear_password" value="1"
                                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">
                                    {{ __('email_settings.edit_config.clear_password_label') }}
                                </span>
                            </label>
                        @endif
                    </div>

                    {{-- Encryption --}}
                    <div>
                        <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.encryption_label') }}
                        </label>
                        <select name="encryption" id="encryption"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @php $enc = old('encryption', $mailSettings->encryption); @endphp
                            <option value="" {{ $enc === null || $enc === '' ? 'selected' : '' }}>{{ __('email_settings.edit_config.encryption_none') }}</option>
                            <option value="tls"  {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl"  {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                        </select>
                    </div>

                    {{-- Timeout --}}
                    <div>
                        <label for="timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.timeout_label') }}
                        </label>
                        <input type="number" name="timeout" id="timeout" min="1" max="600"
                               value="{{ old('timeout', $mailSettings->timeout) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- From address --}}
                    <div>
                        <label for="from_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.from_address_label') }}
                        </label>
                        <input type="email" name="from_address" id="from_address" maxlength="255"
                               value="{{ old('from_address', $mailSettings->from_address) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- From name --}}
                    <div>
                        <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.from_name_label') }}
                        </label>
                        <input type="text" name="from_name" id="from_name" maxlength="255"
                               value="{{ old('from_name', $mailSettings->from_name) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    {{-- Test recipient --}}
                    <div class="md:col-span-2">
                        <label for="test_recipient" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit_config.test_recipient_label') }}
                        </label>
                        <input type="email" name="test_recipient" id="test_recipient" maxlength="255"
                               value="{{ old('test_recipient', $mailSettings->test_recipient) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('email_settings.edit_config.test_recipient_help') }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('email_settings.edit_config.submit_button') }}
                    </button>
                </div>
            </form>
        </section>

        {{-- ============================================================ --}}
        {{-- Section 3: Send test email                                  --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4">
                {{ __('email_settings.test_email.heading') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                {{ __('email_settings.test_email.help_text') }}
            </p>

            <form action="{{ route('admin.email-settings.test-email') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="te-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.test_email.to_label') }}
                        </label>
                        <input type="email" name="to" id="te-to"
                               value="{{ old('to', $mailSettings->test_recipient) }}"
                               placeholder="{{ $mailSettings->test_recipient ?: $mailSettings->from_address }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="te-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.test_email.subject_label') }}
                        </label>
                        <input type="text" name="subject" id="te-subject" maxlength="255"
                               value="{{ old('subject') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label for="te-message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('email_settings.test_email.message_label') }}
                    </label>
                    <textarea name="message" id="te-message" rows="4" maxlength="5000"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('email_settings.test_email.submit_button') }}
                    </button>
                </div>
            </form>
        </section>

        {{-- ============================================================ --}}
        {{-- Section 4: Active template source preview                   --}}
        {{-- ============================================================ --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ __('email_settings.active_source.heading') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ __('email_settings.active_source.help_text') }}
            </p>

            @if ($active && $activeSource)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="inline-block px-2 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200 font-semibold">
                            {{ $active->name }}
                        </span>
                        <span class="inline-block px-2 py-0.5 rounded {{ $activeSource['source_kind'] === 'blade_view' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200' }}">
                            {{ $activeSource['source_kind'] === 'blade_view' ? __('email_settings.active_source.blade_view_kind') : __('email_settings.active_source.inline_html_kind') }}
                        </span>
                        @if ($activeSource['source_kind'] === 'blade_view')
                            <span class="font-mono text-gray-500 dark:text-gray-400 break-all">{{ $activeSource['view_name'] }}</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.email-settings.templates.edit', $active) }}"
                       class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm whitespace-nowrap">
                        {{ __('email_settings.active_source.view_source_button') }}
                    </a>
                </div>

                <pre dir="ltr"
                     class="bg-gray-900 text-gray-100 p-4 rounded-md overflow-x-auto text-xs leading-relaxed font-mono whitespace-pre-wrap break-all"
                     style="max-height: 360px;"><code>{{ $activeSource['content'] }}</code></pre>
            @else
                <div class="rounded-md bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 p-4 text-sm text-yellow-800 dark:text-yellow-200">
                    {{ __('email_settings.active_source.no_active') }}
                    (<span class="font-mono">{{ $defaultView }}</span>)
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.email-settings.default-source') }}"
                       class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                        {{ __('email_settings.default_source.view_button') }}
                    </a>
                </div>
            @endif
        </section>

        {{-- ============================================================ --}}
        {{-- Section 5: Default template source                          --}}
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
        {{-- Section 6: Add new template                                 --}}
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
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{-- Help: which variables are available in the Blade view --}}
                            <strong>Blade variables:</strong>
                            <code>$order</code> (TicketOrder with items/tickets/ticketType),
                            <code>$currencySymbol</code>,
                            <code>$template</code> (current EmailTemplate).
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
        {{-- Section 7: Existing templates                               --}}
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
