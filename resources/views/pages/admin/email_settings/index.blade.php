<x-layouts.app :title="__('email_settings.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="{ tab: @js($tab) }">

        {{-- ============================================================ --}}
        {{-- Header                                                        --}}
        {{-- ============================================================ --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white">
                {{ __('email_settings.main_heading') }}
            </h1>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 p-4 text-sm text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any() && !$errors->has('test_email'))
            <div class="mb-6 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- Tabs                                                          --}}
        {{-- ============================================================ --}}
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex gap-6" role="tablist">
                <button type="button"
                        role="tab"
                        :aria-selected="tab === 'config'"
                        @click="tab = 'config'"
                        :class="tab === 'config'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="py-3 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-t">
                    <span class="inline-flex items-center gap-2">
                        <flux:icon.cog-6-tooth class="w-4 h-4" />
                        {{ __('email_settings.tabs.config') }}
                    </span>
                </button>
                <button type="button"
                        role="tab"
                        :aria-selected="tab === 'templates'"
                        @click="tab = 'templates'"
                        :class="tab === 'templates'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="py-3 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-t">
                    <span class="inline-flex items-center gap-2">
                        <flux:icon.document-duplicate class="w-4 h-4" />
                        {{ __('email_settings.tabs.templates') }}
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-semibold rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                            {{ $templates->count() }}
                        </span>
                    </span>
                </button>
            </nav>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 1: Sending Configuration                                  --}}
        {{-- ============================================================ --}}
        <div x-show="tab === 'config'" x-cloak class="space-y-6">

            {{-- Currently effective (read-only summary) --}}
            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                    {{ __('email_settings.config.currently_effective') }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Mailer:</span>
                        <span class="ml-1 font-mono font-semibold text-gray-900 dark:text-white">{{ strtoupper($config['mailer'] ?: '—') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Host:</span>
                        <span class="ml-1 font-mono text-gray-900 dark:text-white">{{ $config['host'] ?: '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">From:</span>
                        <span class="ml-1 font-mono text-gray-900 dark:text-white">{{ $config['from_address'] ?: '—' }}</span>
                    </div>
                </div>
            </section>

            <form action="{{ route('admin.email-settings.mail-config.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Section: Who sends --}}
                <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">
                        {{ __('email_settings.config.section_sender.heading') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('email_settings.config.section_sender.help') }}
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="from_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('email_settings.config.from_name_label') }}
                            </label>
                            <input type="text" name="from_name" id="from_name" maxlength="255"
                                   value="{{ old('from_name', $mailSettings->from_name) }}"
                                   placeholder="{{ __('email_settings.config.from_name_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="from_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('email_settings.config.from_address_label') }}
                            </label>
                            <input type="email" name="from_address" id="from_address" maxlength="255"
                                   value="{{ old('from_address', $mailSettings->from_address) }}"
                                   placeholder="{{ __('email_settings.config.from_address_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </section>

                {{-- Section: Server (SMTP) --}}
                <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">
                        {{ __('email_settings.config.section_server.heading') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ __('email_settings.config.section_server.help') }}
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="mailer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('email_settings.config.mailer_label') }}
                            </label>
                            <select name="mailer" id="mailer"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('email_settings.config.encryption_none') }} (use .env)</option>
                                @foreach(['smtp','sendmail','log','array'] as $m)
                                    <option value="{{ $m }}" {{ old('mailer', $mailSettings->mailer) === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('email_settings.config.mailer_help') }}</p>
                        </div>

                        <div>
                            <label for="host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.host_label') }}</label>
                            <input type="text" name="host" id="host" maxlength="255"
                                   value="{{ old('host', $mailSettings->host) }}"
                                   placeholder="{{ __('email_settings.config.host_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.port_label') }}</label>
                            <input type="number" name="port" id="port" min="1" max="65535"
                                   value="{{ old('port', $mailSettings->port) }}"
                                   placeholder="{{ __('email_settings.config.port_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.username_label') }}</label>
                            <input type="text" name="username" id="username" maxlength="255" autocomplete="off"
                                   value="{{ old('username', $mailSettings->username) }}"
                                   placeholder="{{ __('email_settings.config.username_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                        </div>

                        <div>
                            <label for="encryption" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.encryption_label') }}</label>
                            @php $enc = old('encryption', $mailSettings->encryption); @endphp
                            <select name="encryption" id="encryption"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="" {{ $enc === null || $enc === '' ? 'selected' : '' }}>{{ __('email_settings.config.encryption_none') }}</option>
                                <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.password_label') }}</label>
                            <input type="password" name="password" id="password" maxlength="1024" autocomplete="new-password"
                                   placeholder="{{ __('email_settings.config.password_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @if (!empty($mailSettings->password_encrypted))
                                <label class="mt-2 inline-flex items-center">
                                    <input type="hidden" name="clear_password" value="0">
                                    <input type="checkbox" name="clear_password" value="1"
                                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">{{ __('email_settings.config.clear_password_label') }}</span>
                                </label>
                            @endif
                        </div>

                        <div>
                            <label for="timeout" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.timeout_label') }}</label>
                            <input type="number" name="timeout" id="timeout" min="1" max="600"
                                   value="{{ old('timeout', $mailSettings->timeout) }}"
                                   placeholder="{{ __('email_settings.config.timeout_placeholder') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            {{ __('email_settings.config.submit_button') }}
                        </button>
                    </div>
                </section>
            </form>

            {{-- Section: Test (separate form so it can be submitted independently) --}}
            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">
                    {{ __('email_settings.config.section_test.heading') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('email_settings.config.section_test.help') }}
                </p>

                @if ($errors->has('test_email'))
                    <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-4 text-sm text-red-800 dark:text-red-200">
                        {{ $errors->first('test_email') }}
                    </div>
                @endif

                <form action="{{ route('admin.email-settings.test-email') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="te-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.test_recipient_label') }}</label>
                        <input type="email" name="to" id="te-to"
                               value="{{ old('to', $mailSettings->test_recipient) }}"
                               placeholder="{{ $mailSettings->test_recipient ?: $mailSettings->from_address }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('email_settings.config.test_recipient_help') }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="te-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.test_subject_label') }}</label>
                            <input type="text" name="subject" id="te-subject" maxlength="255"
                                   value="{{ old('subject') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label for="te-message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.config.test_message_label') }}</label>
                        <textarea name="message" id="te-message" rows="4" maxlength="5000"
                                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            {{ __('email_settings.config.send_test_button') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 2: Templates                                              --}}
        {{-- ============================================================ --}}
        <div x-show="tab === 'templates'" x-cloak class="space-y-4">

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ __('email_settings.templates_list.heading') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('email_settings.templates_list.help_text') }}</p>
                </div>
                <a href="{{ route('admin.email-settings.templates.create') }}"
                   class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 whitespace-nowrap">
                    <flux:icon.plus class="w-4 h-4 mr-1.5" />
                    {{ __('email_settings.templates_list.add_button') }}
                </a>
            </div>

            @if ($templates->isEmpty())
                <div class="bg-white dark:bg-gray-800 p-12 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 text-center">
                    <flux:icon.document-duplicate class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600" />
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ __('email_settings.templates_list.empty') }}</p>
                    <a href="{{ route('admin.email-settings.templates.create') }}"
                       class="mt-4 inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        {{ __('email_settings.templates_list.add_button') }}
                    </a>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/40">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.templates_list.header_name') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300 hidden md:table-cell">{{ __('email_settings.templates_list.header_subject') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300 hidden sm:table-cell">{{ __('email_settings.templates_list.header_source') }}</th>
                                    <th class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.templates_list.header_default') }}</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">{{ __('email_settings.templates_list.header_actions') }}</th>
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
                                            <div class="text-xs text-gray-500 dark:text-gray-400 md:hidden mt-1 truncate max-w-xs">{{ $tpl->subject }}</div>
                                        </td>
                                        <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300 hidden md:table-cell">
                                            <div class="truncate max-w-xs" title="{{ $tpl->subject }}">{{ $tpl->subject }}</div>
                                        </td>
                                        <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300 hidden sm:table-cell">
                                            @if ($tpl->view_name)
                                                <span class="inline-block px-2 py-0.5 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">
                                                    {{ __('email_settings.templates_list.source_view') }}
                                                </span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 text-xs rounded bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200">
                                                    {{ __('email_settings.templates_list.source_html') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 align-top text-center">
                                            @if ($tpl->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                    {{ __('email_settings.templates_list.default_badge') }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 align-top text-right">
                                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                                <a href="{{ route('admin.email-settings.templates.edit', $tpl) }}"
                                                   class="inline-flex items-center px-3 py-1 rounded-md bg-gray-700 dark:bg-gray-600 hover:bg-gray-800 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                    {{ __('email_settings.templates_list.edit_button') }}
                                                </a>

                                                @if (! $tpl->is_active)
                                                    <form action="{{ route('admin.email-settings.templates.activate', $tpl) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                                class="inline-flex items-center px-3 py-1 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                            {{ __('email_settings.templates_list.make_default_button') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('admin.email-settings.templates.duplicate', $tpl) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                        {{ __('email_settings.templates_list.duplicate_button') }}
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.email-settings.templates.destroy', $tpl) }}" method="POST"
                                                      onsubmit="return confirm('{{ __('email_settings.templates_list.delete_confirm', ['name' => $tpl->name]) }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                                        {{ __('email_settings.templates_list.delete_button') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Make sure Alpine x-cloak hides elements until Alpine is ready. --}}
    <style>[x-cloak] { display: none !important; }</style>
</x-layouts.app>
