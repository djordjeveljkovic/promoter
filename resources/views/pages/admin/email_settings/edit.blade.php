<x-layouts.app :title="__('email_settings.editor.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('admin.email-settings.index') }}"
                   class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline mb-2">
                    &larr; {{ __('email_settings.editor.back_to_list') }}
                </a>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                    @if ($isDefault)
                        {{ __('email_settings.editor.default_heading') }}
                    @else
                        {{ __('email_settings.editor.heading', ['name' => $template->name]) }}
                    @endif
                </h1>
                @if ($isDefault)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.editor.default_subtitle', ['view' => $defaultView]) }}
                    </p>
                @else
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('email_settings.editor.subtitle', ['view' => $template->view_name ?? __('email_settings.editor.inline_html')]) }}
                    </p>
                @endif
            </div>

            @if (! $isDefault)
                <div class="flex flex-wrap gap-2">
                    <form action="{{ route('admin.email-settings.templates.duplicate', $template) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                            {{ __('email_settings.editor.duplicate_button') }}
                        </button>
                    </form>

                    @if (! $template->is_active)
                        <form action="{{ route('admin.email-settings.templates.activate', $template) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                                {{ __('email_settings.editor.activate_button') }}
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center px-3 py-2 rounded-md bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200 text-sm font-medium">
                            {{ __('email_settings.editor.active_badge') }}
                        </span>
                    @endif
                </div>
            @else
                <div>
                    <button type="button"
                            id="open-import-modal"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                        {{ __('email_settings.editor.import_button') }}
                    </button>
                </div>
            @endif
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

        {{-- Template metadata card --}}
        @if (! $isDefault)
            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    {{ __('email_settings.editor.metadata_heading') }}
                </h2>
                <form action="{{ route('admin.email-settings.templates.update', $template) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.name_label') }}
                        </label>
                        <input type="text" name="name" id="name" required maxlength="255"
                               value="{{ old('name', $template->name) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.subject_label') }}
                        </label>
                        <input type="text" name="subject" id="subject" required maxlength="255"
                               value="{{ old('subject', $template->subject) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.description_label') }}
                        </label>
                        <input type="text" name="description" id="description" maxlength="500"
                               value="{{ old('description', $template->description) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-gray-700 dark:bg-gray-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-800">
                            {{ __('email_settings.editor.save_metadata_button') }}
                        </button>
                    </div>
                </form>
            </section>
        @endif

        {{-- Source code editor --}}
        <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    {{ __('email_settings.editor.source_heading') }}
                </h2>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    @if ($source['view_name'])
                        <span class="font-mono break-all">{{ $source['view_name'] }}</span>
                    @else
                        <span>{{ __('email_settings.editor.inline_html') }}</span>
                    @endif
                    &nbsp;&middot;&nbsp;
                    {{ __('email_settings.editor.source_size', ['size' => number_format($source['size'] / 1024, 2)]) }}
                </div>
            </div>

            @if (! $source['exists'])
                <div class="mb-4 rounded-md bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 p-4 text-sm text-yellow-800 dark:text-yellow-200">
                    {{ __('email_settings.editor.source_missing', ['path' => $source['absolute_path'] ?? '']) }}
                </div>
            @endif

            @if ($isDefault)
                <div class="mb-4 rounded-md bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 p-4 text-sm text-blue-800 dark:text-blue-200">
                    {{ __('email_settings.editor.default_readonly_hint') }}
                </div>
                <pre dir="ltr"
                     class="bg-gray-900 text-gray-100 p-4 rounded-md overflow-x-auto text-xs leading-relaxed font-mono whitespace-pre"
                     style="max-height: 70vh;"><code>{{ $source['content'] }}</code></pre>
            @else
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    {{ __('email_settings.editor.editor_hint') }}
                </p>
                <form action="{{ route('admin.email-settings.templates.source.update', $template) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex gap-3" style="min-height: 60vh;">
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
                                  class="flex-1 block w-full font-mono text-xs leading-relaxed bg-gray-900 text-gray-100 border border-gray-300 dark:border-gray-700 rounded-r-md p-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y"
                                  style="min-height: 60vh; tab-size: 4; -moz-tab-size: 4;"
                                  wrap="off">{{ $source['content'] }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <a href="{{ route('admin.email-settings.index') }}"
                           class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                            {{ __('email_settings.editor.cancel_button') }}
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                                onclick="return confirm('{{ __('email_settings.editor.save_confirm') }}')">
                            {{ __('email_settings.editor.save_source_button') }}
                        </button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Preview link --}}
        @if (! $isDefault && $source['view_name'])
            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                <h2 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('email_settings.editor.preview_heading') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('email_settings.editor.preview_help') }}
                </p>
                <a href="{{ $template->is_active ? '#' : '#' }}"
                   class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    {{ __('email_settings.editor.preview_link') }}
                </a>
            </section>
        @endif

        {{-- Delete template --}}
        @if (! $isDefault)
            <section class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border border-red-200 dark:border-red-900/40">
                <h2 class="text-lg font-semibold text-red-700 dark:text-red-400 mb-2">
                    {{ __('email_settings.editor.danger_heading') }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('email_settings.editor.danger_help') }}
                </p>
                <form action="{{ route('admin.email-settings.templates.destroy', $template) }}" method="POST"
                      onsubmit="return confirm('{{ __('email_settings.editor.delete_confirm', ['name' => $template->name]) }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700">
                        {{ __('email_settings.editor.delete_button') }}
                    </button>
                </form>
            </section>
        @endif
    </div>

    {{-- Import modal (only on default source view) --}}
    @if ($isDefault)
        <div id="import-modal" class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ __('email_settings.editor.import_modal_heading') }}
                </h3>
                <form action="{{ route('admin.email-settings.import-default') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="imp-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.name_label') }}
                        </label>
                        <input type="text" name="name" id="imp-name" required maxlength="255"
                               value="{{ old('name', __('email_settings.editor.default_import_name')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="imp-subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.subject_label') }}
                        </label>
                        <input type="text" name="subject" id="imp-subject" required maxlength="255"
                               value="{{ old('subject', __('email_settings.editor.default_import_subject')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="imp-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.add_template.description_label') }}
                        </label>
                        <input type="text" name="description" id="imp-description" maxlength="500"
                               value="{{ old('description') }}"
                               placeholder="{{ __('email_settings.editor.import_default_description_placeholder') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <label class="inline-flex items-center">
                        <input type="hidden" name="activate" value="0">
                        <input type="checkbox" name="activate" value="1"
                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ __('email_settings.editor.activate_after_import') }}
                        </span>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" id="close-import-modal"
                                class="inline-flex items-center justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm">
                            {{ __('email_settings.editor.cancel_button') }}
                        </button>
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                            {{ __('email_settings.editor.import_button') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('import-modal');
                const openBtn = document.getElementById('open-import-modal');
                const closeBtn = document.getElementById('close-import-modal');
                if (!modal || !openBtn || !closeBtn) return;

                const show = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
                const hide = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };

                openBtn.addEventListener('click', show);
                closeBtn.addEventListener('click', hide);
                modal.addEventListener('click', (e) => { if (e.target === modal) hide(); });
            })();
        </script>
    @endif

    {{-- Editor niceties: Tab key, synced line numbers height --}}
    @if (! $isDefault)
        <script>
            (function () {
                const editor = document.getElementById('source-editor');
                const gutter = document.getElementById('line-numbers');
                if (!editor || !gutter) return;

                // Keep gutter height in sync with the textarea content height.
                const sync = () => {
                    gutter.style.height = editor.scrollHeight + 'px';
                    gutter.scrollTop = editor.scrollTop;
                };
                editor.addEventListener('input', sync);
                editor.addEventListener('scroll', () => { gutter.scrollTop = editor.scrollTop; });
                sync();

                // Tab key inserts spaces.
                editor.addEventListener('keydown', function (e) {
                    if (e.key !== 'Tab') return;
                    e.preventDefault();
                    const start = editor.selectionStart;
                    const end   = editor.selectionEnd;
                    editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                    editor.selectionStart = editor.selectionEnd = start + 4;
                });
            })();
        </script>
    @endif
</x-layouts.app>
