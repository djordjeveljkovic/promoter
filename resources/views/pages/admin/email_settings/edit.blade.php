<x-layouts.app :title="__('email_settings.edit.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">

        {{-- ============================================================ --}}
        {{-- Top bar: back, name, subject, description, save              --}}
        {{-- ============================================================ --}}
        <div class="mb-6">
            <a href="{{ route('admin.email-settings.index', ['tab' => 'templates']) }}"
               class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                <flux:icon.arrow-left class="w-4 h-4 mr-1" />
                {{ __('email_settings.edit.back_to_list') }}
            </a>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 p-3 text-sm text-green-800 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-3 text-sm text-red-800 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 p-3 text-sm text-red-800 dark:text-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============================================================ --}}
        {{-- Metadata card (name, subject, description, default checkbox) --}}
        {{-- ============================================================ --}}
        <form action="{{ route('admin.email-settings.templates.update', $template) }}" method="POST"
              class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6"
              x-data="{ makeDefault: @js((bool) old('is_active', $template->is_active)) }">
            @csrf
            @method('PUT')

            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit.name_label') }}
                        </label>
                        <input type="text" name="name" id="name" required maxlength="255"
                               value="{{ old('name', $template->name) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit.subject_label') }}
                        </label>
                        <input type="text" name="subject" id="subject" required maxlength="255"
                               value="{{ old('subject', $template->subject) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit.description_label') }}
                        </label>
                        <input type="text" name="description" id="description" maxlength="500"
                               value="{{ old('description', $template->description) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="lg:w-72 flex-shrink-0">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('email_settings.edit.make_default_label') }}
                    </label>
                    <label class="flex items-start gap-3 p-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900/60">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-model="makeDefault"
                               class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.edit.make_default_help') }}
                        </span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-700 dark:bg-gray-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('email_settings.edit.save_metadata_button') }}
                </button>
            </div>
        </form>

        {{-- ============================================================ --}}
        {{-- Split view: code editor on the left, preview on the right    --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- LEFT: source code editor --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col"
                 x-data="emailEditor()">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
                            {{ __('email_settings.edit.editor_heading') }}
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
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
                    <div class="m-4 rounded-md bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 p-3 text-xs text-yellow-800 dark:text-yellow-200">
                        {{ __('email_settings.edit.source_missing', ['path' => $source['absolute_path'] ?? '']) }}
                    </div>
                @endif

                <form action="{{ route('admin.email-settings.templates.source.update', $template) }}" method="POST" class="flex flex-col flex-1"
                      @submit="confirmSave($event)">
                    @csrf
                    @method('PUT')

                    <div class="flex-1 flex" style="min-height: 70vh;">
                        {{-- Line numbers gutter --}}
                        <div id="line-numbers"
                             class="select-none text-right text-gray-400 dark:text-gray-500 font-mono text-xs leading-relaxed bg-gray-50 dark:bg-gray-900 border border-r-0 border-gray-300 dark:border-gray-700 rounded-l-md px-2 py-3 overflow-hidden"
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
                                  class="flex-1 block w-full font-mono text-xs leading-relaxed bg-gray-900 text-gray-100 border border-gray-300 dark:border-gray-700 rounded-r-md p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"
                                  style="min-height: 70vh; tab-size: 4; -moz-tab-size: 4;"
                                  wrap="off">{{ $source['content'] }}</textarea>
                    </div>

                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                            {!! __('email_settings.edit.editor_blade_variables') !!}
                        </p>
                        <div class="flex justify-end gap-2">
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                {{ __('email_settings.edit.save_source_button') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- RIGHT: live preview --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
                            {{ __('email_settings.edit.preview_heading') }}
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ __('email_settings.edit.preview_help') }}
                        </p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('preview-iframe').src = document.getElementById('preview-iframe').src.split('?')[0] + '?t=' + Date.now();"
                            class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <flux:icon.arrow-path class="w-3.5 h-3.5 mr-1" />
                        {{ __('email_settings.edit.preview_refresh_button') }}
                    </button>
                </div>

                <div class="flex-1 bg-gray-100 dark:bg-gray-900" style="min-height: 70vh;">
                    <iframe id="preview-iframe"
                            src="{{ route('admin.email-settings.templates.preview', $template) }}"
                            title="{{ __('email_settings.edit.preview_iframe_title') }}"
                            class="w-full h-full bg-white"
                            style="min-height: 70vh; border: 0;"></iframe>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- Danger zone                                                    --}}
        {{-- ============================================================ --}}
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-900/40 p-5">
            <h2 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-1">
                {{ __('email_settings.edit.danger_heading') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ __('email_settings.edit.danger_help') }}
            </p>
            <form action="{{ route('admin.email-settings.templates.destroy', $template) }}" method="POST"
                  onsubmit="return confirm('{{ __('email_settings.edit.delete_confirm', ['name' => $template->name]) }}')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    {{ __('email_settings.edit.delete_button') }}
                </button>
            </form>
        </div>
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
