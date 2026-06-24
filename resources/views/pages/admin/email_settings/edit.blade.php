<x-layouts.app :title="__('email_settings.edit.page_title')">
    <div class="space-y-6">

        {{-- ============================================================ --}}
        {{-- Top bar: back link                                            --}}
        {{-- ============================================================ --}}
        <div>
            <x-ui.link variant="primary" :href="route('admin.email-settings.index', ['tab' => 'templates'])" icon="arrow-left">
                {{ __('email_settings.edit.back_to_list') }}
            </x-ui.link>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any())
            <x-ui.alert variant="danger">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        {{-- ============================================================ --}}
        {{-- Metadata card (name, subject, description, default checkbox) --}}
        {{-- ============================================================ --}}
        <x-ui.card>
            <form action="{{ route('admin.email-settings.templates.update', $template) }}" method="POST"
                  class="p-5"
                  x-data="{ makeDefault: @js((bool) old('is_active', $template->is_active)) }">
                @csrf
                @method('PUT')

                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.field :label="__('email_settings.edit.name_label')" for="name" required>
                            <x-ui.input id="name" name="name" type="text" required maxlength="255"
                                        :value="old('name', $template->name)" />
                        </x-ui.field>

                        <x-ui.field :label="__('email_settings.edit.subject_label')" for="subject" required>
                            <x-ui.input id="subject" name="subject" type="text" required maxlength="255"
                                        :value="old('subject', $template->subject)" />
                        </x-ui.field>

                        <div class="md:col-span-2">
                            <x-ui.field :label="__('email_settings.edit.description_label')" for="description">
                                <x-ui.input id="description" name="description" type="text" maxlength="500"
                                            :value="old('description', $template->description)" />
                            </x-ui.field>
                        </div>
                    </div>

                    <div class="lg:w-72 flex-shrink-0">
                        <p class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            {{ __('email_settings.edit.make_default_label') }}
                        </p>
                        <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/40">
                            <input type="hidden" name="is_active" value="0">
                            <x-ui.checkbox name="is_active" value="1" :label="__('email_settings.edit.make_default_help')" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-4">
                    <x-ui.button variant="secondary" type="submit">
                        {{ __('email_settings.edit.save_metadata_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- ============================================================ --}}
        {{-- Split view: code editor on the left, preview on the right    --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- LEFT: source code editor --}}
            <x-ui.card class="flex flex-col" x-data="emailEditor()">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-800 dark:text-white">
                            {{ __('email_settings.edit.editor_heading') }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            @if ($source['view_name'])
                                <span class="font-mono">{{ $source['view_name'] }}</span>
                            @else
                                {{ __('email_settings.templates_list.source_html') }}
                            @endif
                            &nbsp;&middot;&nbsp;
                            {{ __('email_settings.edit.source_size', ['size' => number_format($source['size'] / 1024, 2)]) }}
                        </p>
                    </div>
                </div>

                @if (!$source['exists'])
                    <div class="m-4">
                        <x-ui.alert variant="warning">
                            {{ __('email_settings.edit.source_missing', ['path' => $source['absolute_path'] ?? '']) }}
                        </x-ui.alert>
                    </div>
                @endif

                <form action="{{ route('admin.email-settings.templates.source.update', $template) }}" method="POST" class="flex flex-col flex-1"
                      @submit="confirmSave($event)">
                    @csrf
                    @method('PUT')

                    <div class="flex-1 flex" style="min-height: 70vh;">
                        {{-- Line numbers gutter --}}
                        <div id="line-numbers"
                             class="select-none text-right text-zinc-400 dark:text-zinc-500 font-mono text-xs leading-relaxed bg-zinc-50 dark:bg-zinc-900 border border-r-0 border-zinc-300 dark:border-zinc-700 rounded-l-lg px-2 py-3 overflow-hidden"
                             style="min-width: 3rem;">
                            @php
                                $lineCount = max(1, substr_count($source['content'] ?? '', "\n") + 1);
                            @endphp
                            @for ($i = 1; $i <= $lineCount; $i++)
                                <div>{{ $i }}</div>
                            @endfor
                        </div>

                        {{-- Editor textarea --}}
                        <textarea name="content" id="source-editor" spellcheck="false"
                                  @input="sync()"
                                  class="flex-1 block w-full font-mono text-xs leading-relaxed bg-zinc-900 text-zinc-100 border border-zinc-300 dark:border-zinc-700 rounded-r-lg p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0 resize-none"
                                  style="min-height: 70vh; tab-size: 4; -moz-tab-size: 4;"
                                  wrap="off">{{ $source['content'] }}</textarea>
                    </div>

                    <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/40">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-2">
                            {!! __('email_settings.edit.editor_blade_variables') !!}
                        </p>
                        <div class="flex justify-end gap-2">
                            <x-ui.button type="submit" variant="primary">
                                {{ __('email_settings.edit.save_source_button') }}
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card>

            {{-- RIGHT: live preview --}}
            <x-ui.card class="flex flex-col">
                <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-zinc-800 dark:text-white">
                            {{ __('email_settings.edit.preview_heading') }}
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ __('email_settings.edit.preview_help') }}
                        </p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('preview-iframe').src = document.getElementById('preview-iframe').src.split('?')[0] + '?t=' + Date.now();"
                            class="inline-flex items-center justify-center rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 px-3 py-1.5 text-xs font-medium text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <x-ui.icon name="arrow-path" class="w-3.5 h-3.5 mr-1" />
                        {{ __('email_settings.edit.preview_refresh_button') }}
                    </button>
                </div>

                <div class="flex-1 bg-zinc-100 dark:bg-zinc-900" style="min-height: 70vh;">
                    <iframe id="preview-iframe"
                            src="{{ route('admin.email-settings.templates.preview', $template) }}"
                            title="{{ __('email_settings.edit.preview_iframe_title') }}"
                            class="w-full h-full bg-white"
                            style="min-height: 70vh; border: 0;"></iframe>
                </div>
            </x-ui.card>
        </div>

        {{-- ============================================================ --}}
        {{-- Danger zone                                                    --}}
        {{-- ============================================================ --}}
        <x-ui.card class="border-rose-200 dark:border-rose-900/40">
            <div class="p-5">
                <h2 class="text-sm font-semibold text-rose-700 dark:text-rose-400 mb-1">
                    {{ __('email_settings.edit.danger_heading') }}
                </h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-4">
                    {{ __('email_settings.edit.danger_help') }}
                </p>
                <form action="{{ route('admin.email-settings.templates.destroy', $template) }}" method="POST"
                      onsubmit="return confirm('{{ __('email_settings.edit.delete_confirm', ['name' => $template->name]) }}')">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="danger">
                        {{ __('email_settings.edit.delete_button') }}
                    </x-ui.button>
                </form>
            </div>
        </x-ui.card>
    </div>

    {{-- Editor niceties: Tab key, synced line numbers height, save confirm --}}
    <script>
        function emailEditor() {
            return {
                sync() {
                    const editor = document.getElementById('source-editor');
                    const gutter = document.getElementById('line-numbers');
                    if (!editor || !gutter) return;

                    // Sync gutter height with editor scroll height.
                    gutter.style.height = editor.scrollHeight + 'px';

                    // Rebuild line numbers so deleted/added lines stay in sync.
                    const lineCount = editor.value.split('\n').length;
                    let html = '';
                    for (let i = 1; i <= lineCount; i++) html += '<div>' + i + '</div>';
                    gutter.innerHTML = html;
                },
                confirmSave(event) {
                    // Soft confirmation so an admin doesn't accidentally
                    // overwrite their template.
                    return confirm(@js(__('email_settings.edit.save_source_button')) + '?');
                }
            };
        }
        window.emailEditor = emailEditor;

        // Tab key inserts 4 spaces in the editor.
        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('source-editor');
            const gutter = document.getElementById('line-numbers');
            if (!editor || !gutter) return;

            // Initial sync (server-rendered gutter counts lines of saved source).
            gutter.style.height = editor.scrollHeight + 'px';
            editor.addEventListener('scroll', () => { gutter.scrollTop = editor.scrollTop; });

            editor.addEventListener('keydown', function (e) {
                if (e.key !== 'Tab') return;
                e.preventDefault();
                const start = editor.selectionStart;
                const end   = editor.selectionEnd;
                editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                editor.selectionStart = editor.selectionEnd = start + 4;
            });
        });
    </script>
</x-layouts.app>
</content>
</invoke>