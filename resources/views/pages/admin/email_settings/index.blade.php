<x-layouts.app :title="__('email_settings.page_title')">
    <div class="space-y-6" x-data="{ tab: @js($tab) }">

        {{-- ============================================================ --}}
        {{-- Header                                                        --}}
        {{-- ============================================================ --}}
        <x-ui.page-header :title="__('email_settings.main_heading')" />

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any() && !$errors->has('test_email'))
            <x-ui.alert variant="danger">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- ============================================================ --}}
        {{-- Tabs                                                          --}}
        {{-- ============================================================ --}}
        <div class="border-b border-zinc-200 dark:border-zinc-700">
            <nav class="-mb-px flex gap-6" role="tablist">
                <button type="button"
                        role="tab"
                        :aria-selected="tab === 'config'"
                        @click="tab = 'config'"
                        :class="tab === 'config'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="py-3 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-t">
                    <span class="inline-flex items-center gap-2">
                        <x-ui.icon name="cog" class="w-4 h-4" />
                        {{ __('email_settings.tabs.config') }}
                    </span>
                </button>
                <button type="button"
                        role="tab"
                        :aria-selected="tab === 'templates'"
                        @click="tab = 'templates'"
                        :class="tab === 'templates'
                            ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                            : 'border-transparent text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300 hover:border-zinc-300 dark:hover:border-zinc-600'"
                        class="py-3 px-1 border-b-2 font-medium text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-t">
                    <span class="inline-flex items-center gap-2">
                        <x-ui.icon name="document-duplicate" class="w-4 h-4" />
                        {{ __('email_settings.tabs.templates') }}
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-semibold rounded-full bg-zinc-100 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200">
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
            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">
                        {{ __('email_settings.config.currently_effective') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div>
                            <span class="text-zinc-500 dark:text-zinc-400">Mailer:</span>
                            <span class="ml-1 font-mono font-semibold text-zinc-900 dark:text-zinc-100">{{ strtoupper($config['mailer'] ?: '—') }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 dark:text-zinc-400">Host:</span>
                            <span class="ml-1 font-mono text-zinc-900 dark:text-zinc-100">{{ $config['host'] ?: '—' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 dark:text-zinc-400">From:</span>
                            <span class="ml-1 font-mono text-zinc-900 dark:text-zinc-100">{{ $config['from_address'] ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <form action="{{ route('admin.email-settings.mail-config.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Section: Who sends --}}
                <x-ui.card>
                    <div class="p-6">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('email_settings.config.section_sender.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('email_settings.config.section_sender.help') }}
                        </p>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-ui.field :label="__('email_settings.config.from_name_label')" for="from_name">
                                <x-ui.input id="from_name" name="from_name" type="text" maxlength="255"
                                            :value="old('from_name', $mailSettings->from_name)"
                                            :placeholder="__('email_settings.config.from_name_placeholder')" />
                            </x-ui.field>
                            <x-ui.field :label="__('email_settings.config.from_address_label')" for="from_address">
                                <x-ui.input id="from_address" name="from_address" type="email" maxlength="255"
                                            :value="old('from_address', $mailSettings->from_address)"
                                            :placeholder="__('email_settings.config.from_address_placeholder')" />
                            </x-ui.field>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Section: Server (SMTP) --}}
                <x-ui.card>
                    <div class="p-6">
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('email_settings.config.section_server.heading') }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('email_settings.config.section_server.help') }}
                        </p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-ui.field :label="__('email_settings.config.mailer_label')" for="mailer" :hint="__('email_settings.config.mailer_help')">
                                    <x-ui.select id="mailer" name="mailer">
                                        <option value="">{{ __('email_settings.config.encryption_none') }} (use .env)</option>
                                        @foreach(['smtp','sendmail','log','array'] as $m)
                                            <option value="{{ $m }}" {{ old('mailer', $mailSettings->mailer) === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                                        @endforeach
                                    </x-ui.select>
                                </x-ui.field>
                            </div>

                            <x-ui.field :label="__('email_settings.config.host_label')" for="host">
                                <x-ui.input id="host" name="host" type="text" maxlength="255"
                                            :value="old('host', $mailSettings->host)"
                                            :placeholder="__('email_settings.config.host_placeholder')"
                                            class="font-mono" />
                            </x-ui.field>

                            <x-ui.field :label="__('email_settings.config.port_label')" for="port">
                                <x-ui.input id="port" name="port" type="number" min="1" max="65535"
                                            :value="old('port', $mailSettings->port)"
                                            :placeholder="__('email_settings.config.port_placeholder')"
                                            class="font-mono" />
                            </x-ui.field>

                            <x-ui.field :label="__('email_settings.config.username_label')" for="username">
                                <x-ui.input id="username" name="username" type="text" maxlength="255" autocomplete="off"
                                            :value="old('username', $mailSettings->username)"
                                            :placeholder="__('email_settings.config.username_placeholder')"
                                            class="font-mono" />
                            </x-ui.field>

                            <x-ui.field :label="__('email_settings.config.encryption_label')" for="encryption">
                                @php $enc = old('encryption', $mailSettings->encryption); @endphp
                                <x-ui.select id="encryption" name="encryption">
                                    <option value="" {{ $enc === null || $enc === '' ? 'selected' : '' }}>{{ __('email_settings.config.encryption_none') }}</option>
                                    <option value="tls" {{ $enc === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $enc === 'ssl' ? 'selected' : '' }}>SSL</option>
                                </x-ui.select>
                            </x-ui.field>

                            <div class="md:col-span-2">
                                <x-ui.field :label="__('email_settings.config.password_label')" for="password">
                                    <x-ui.input id="password" name="password" type="password" maxlength="1024" autocomplete="new-password"
                                                :placeholder="__('email_settings.config.password_placeholder')" />
                                </x-ui.field>
                                @if (!empty($mailSettings->password_encrypted))
                                    <input type="hidden" name="clear_password" value="0">
                                    <x-ui.checkbox name="clear_password" value="1" :label="__('email_settings.config.clear_password_label')" class="mt-2" />
                                @endif
                            </div>

                            <x-ui.field :label="__('email_settings.config.timeout_label')" for="timeout">
                                <x-ui.input id="timeout" name="timeout" type="number" min="1" max="600"
                                            :value="old('timeout', $mailSettings->timeout)"
                                            :placeholder="__('email_settings.config.timeout_placeholder')"
                                            class="font-mono" />
                            </x-ui.field>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-ui.button type="submit" variant="primary">
                                {{ __('email_settings.config.submit_button') }}
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.card>
            </form>

            {{-- Section: Test (separate form so it can be submitted independently) --}}
            <x-ui.card>
                <div class="p-6">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('email_settings.config.section_test.heading') }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('email_settings.config.section_test.help') }}
                    </p>

                    @if ($errors->has('test_email'))
                        <div class="mt-4">
                            <x-ui.alert variant="danger">{{ $errors->first('test_email') }}</x-ui.alert>
                        </div>
                    @endif

                    <form action="{{ route('admin.email-settings.test-email') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <x-ui.field :label="__('email_settings.config.test_recipient_label')" for="te-to" :hint="__('email_settings.config.test_recipient_help')">
                            <x-ui.input id="te-to" name="to" type="email"
                                        :value="old('to', $mailSettings->test_recipient)"
                                        :placeholder="$mailSettings->test_recipient ?: $mailSettings->from_address" />
                        </x-ui.field>
                        <x-ui.field :label="__('email_settings.config.test_subject_label')" for="te-subject">
                            <x-ui.input id="te-subject" name="subject" type="text" maxlength="255" :value="old('subject')" />
                        </x-ui.field>
                        <x-ui.field :label="__('email_settings.config.test_message_label')" for="te-message">
                            <x-ui.textarea id="te-message" name="message" rows="4" maxlength="5000">{{ old('message') }}</x-ui.textarea>
                        </x-ui.field>
                        <div class="flex justify-end">
                            <x-ui.button type="submit" variant="success">
                                {{ __('email_settings.config.send_test_button') }}
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </x-ui.card>
        </div>

        {{-- ============================================================ --}}
        {{-- TAB 2: Templates                                              --}}
        {{-- ============================================================ --}}
        <div x-show="tab === 'templates'" x-cloak class="space-y-4">

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('email_settings.templates_list.heading') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('email_settings.templates_list.help_text') }}</p>
                </div>
                <x-ui.button variant="primary" :href="route('admin.email-settings.templates.create')" icon="plus">
                    {{ __('email_settings.templates_list.add_button') }}
                </x-ui.button>
            </div>

            @if ($templates->isEmpty())
                <x-ui.card>
                    <x-ui.empty-state
                        icon="document-duplicate"
                        :title="__('email_settings.templates_list.empty')"
                    >
                        <x-slot:actions>
                            <x-ui.button variant="primary" :href="route('admin.email-settings.templates.create')" icon="plus">
                                {{ __('email_settings.templates_list.add_button') }}
                            </x-ui.button>
                        </x-slot:actions>
                    </x-ui.empty-state>
                </x-ui.card>
            @else
                <x-ui.card :padding="false">
                    <x-ui.table>
                        <x-ui.table-header>
                            <x-ui.table-row>
                                <x-ui.table-cell header>{{ __('email_settings.templates_list.header_name') }}</x-ui.table-cell>
                                <x-ui.table-cell header class="hidden md:table-cell">{{ __('email_settings.templates_list.header_subject') }}</x-ui.table-cell>
                                <x-ui.table-cell header class="hidden sm:table-cell">{{ __('email_settings.templates_list.header_source') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="center">{{ __('email_settings.templates_list.header_default') }}</x-ui.table-cell>
                                <x-ui.table-cell header align="right">{{ __('email_settings.templates_list.header_actions') }}</x-ui.table-cell>
                            </x-ui.table-row>
                        </x-ui.table-header>
                        <x-ui.table-body>
                            @foreach ($templates as $tpl)
                                <x-ui.table-row>
                                    <x-ui.table-cell>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $tpl->name }}</div>
                                        @if ($tpl->description)
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $tpl->description }}</div>
                                        @endif
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 md:hidden mt-1 truncate max-w-xs">{{ $tpl->subject }}</div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="hidden md:table-cell">
                                        <div class="truncate max-w-xs" title="{{ $tpl->subject }}">{{ $tpl->subject }}</div>
                                    </x-ui.table-cell>
                                    <x-ui.table-cell class="hidden sm:table-cell">
                                        @if ($tpl->view_name)
                                            <x-ui.badge variant="info" size="sm">
                                                {{ __('email_settings.templates_list.source_view') }}
                                            </x-ui.badge>
                                        @else
                                            <x-ui.badge variant="indigo" size="sm">
                                                {{ __('email_settings.templates_list.source_html') }}
                                            </x-ui.badge>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="center">
                                        @if ($tpl->is_active)
                                            <x-ui.badge variant="success" size="sm">
                                                {{ __('email_settings.templates_list.default_badge') }}
                                            </x-ui.badge>
                                        @else
                                            <span class="text-xs text-zinc-400 dark:text-zinc-500">—</span>
                                        @endif
                                    </x-ui.table-cell>
                                    <x-ui.table-cell align="right">
                                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                            <x-ui.button variant="secondary" size="sm" :href="route('admin.email-settings.templates.edit', $tpl)">
                                                {{ __('email_settings.templates_list.edit_button') }}
                                            </x-ui.button>

                                            @if (! $tpl->is_active)
                                                <form action="{{ route('admin.email-settings.templates.activate', $tpl) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <x-ui.button type="submit" variant="primary" size="sm">
                                                        {{ __('email_settings.templates_list.make_default_button') }}
                                                    </x-ui.button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.email-settings.templates.duplicate', $tpl) }}" method="POST">
                                                @csrf
                                                <x-ui.button type="submit" variant="secondary" size="sm">
                                                    {{ __('email_settings.templates_list.duplicate_button') }}
                                                </x-ui.button>
                                            </form>

                                            <form action="{{ route('admin.email-settings.templates.destroy', $tpl) }}" method="POST"
                                                  onsubmit="return confirm('{{ __('email_settings.templates_list.delete_confirm', ['name' => $tpl->name]) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="danger" size="sm">
                                                    {{ __('email_settings.templates_list.delete_button') }}
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    </x-ui.table-cell>
                                </x-ui.table-row>
                            @endforeach
                        </x-ui.table-body>
                    </x-ui.table>
                </x-ui.card>
            @endif
        </div>
    </div>

    {{-- Make sure Alpine x-cloak hides elements until Alpine is ready. --}}
    <style>[x-cloak] { display: none !important; }</style>
</x-layouts.app>