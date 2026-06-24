<x-layouts.app :title="__('email_settings.create.page_title')">
    <div class="space-y-6 max-w-3xl" x-data="{ sourceType: @js(old('source_type', 'view')) }">

        <x-ui.page-header
            :title="__('email_settings.create.heading')"
            :subtitle="__('email_settings.create.help_text')"
        >
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('admin.email-settings.index', ['tab' => 'templates'])" icon="arrow-left">
                    {{ __('email_settings.create.back_to_list') }}
                </x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($errors->any())
            <x-ui.alert variant="danger">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form action="{{ route('admin.email-settings.templates.store') }}" method="POST" class="space-y-5 p-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-ui.field :label="__('email_settings.create.name_label')" for="name" required>
                        <x-ui.input id="name" name="name" type="text" required maxlength="255"
                                    :value="old('name')"
                                    :placeholder="__('email_settings.create.name_placeholder')" />
                    </x-ui.field>

                    <x-ui.field :label="__('email_settings.create.subject_label')" for="subject" required>
                        <x-ui.input id="subject" name="subject" type="text" required maxlength="255"
                                    :value="old('subject')"
                                    :placeholder="__('email_settings.create.subject_placeholder')" />
                    </x-ui.field>
                </div>

                <x-ui.field :label="__('email_settings.create.description_label')" for="description">
                    <x-ui.input id="description" name="description" type="text" maxlength="500"
                                :value="old('description')"
                                :placeholder="__('email_settings.create.description_placeholder')" />
                </x-ui.field>

                {{-- Source type radio --}}
                <fieldset>
                    <legend class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                        {{ __('email_settings.create.source_type_label') }}
                    </legend>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/40 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900/60">
                            <input type="radio" name="source_type" value="view" x-model="sourceType"
                                   class="mt-0.5 border-zinc-300 dark:border-zinc-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-0 dark:bg-zinc-900">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                {{ __('email_settings.create.source_type_view') }}
                            </span>
                        </label>
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/40 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-900/60">
                            <input type="radio" name="source_type" value="html" x-model="sourceType"
                                   class="mt-0.5 border-zinc-300 dark:border-zinc-600 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-0 dark:bg-zinc-900">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                {{ __('email_settings.create.source_type_html') }}
                            </span>
                        </label>
                    </div>
                </fieldset>

                {{-- Blade view path (visible when source_type=view) --}}
                <div x-show="sourceType === 'view'" x-cloak>
                    <x-ui.field :label="__('email_settings.create.view_name_label')" for="view_name">
                        <x-ui.input id="view_name" name="view_name" type="text" maxlength="255"
                                    list="available-views"
                                    :value="old('view_name')"
                                    :placeholder="__('email_settings.create.view_name_placeholder')"
                                    autocomplete="off"
                                    class="font-mono text-sm" />
                        {{-- Datalist of all Blade views that already exist under
                             resources/views/emails/. Picking one "links" the file
                             as-is (no copy). Leave empty to auto-create a new file
                             from the default template. --}}
                        <datalist id="available-views">
                            @foreach (($availableViews ?? []) as $existingView)
                                <option value="{{ $existingView }}"></option>
                            @endforeach
                        </datalist>
                    </x-ui.field>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                        {!! __('email_settings.create.view_name_help', ['default' => $defaultView]) !!}
                    </p>
                </div>

                {{-- HTML content (visible when source_type=html) --}}
                <div x-show="sourceType === 'html'" x-cloak>
                    <x-ui.field :label="__('email_settings.create.html_content_label')" for="html_content">
                        <x-ui.textarea id="html_content" name="html_content" rows="8"
                                       :placeholder="__('email_settings.create.html_content_placeholder')"
                                       class="font-mono text-sm">{{ old('html_content') }}</x-ui.textarea>
                    </x-ui.field>
                </div>

                <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/40">
                    <input type="hidden" name="is_active" value="0">
                    <x-ui.checkbox name="is_active" value="1" :label="__('email_settings.create.make_default_label')" />
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <x-ui.button variant="secondary" :href="route('admin.email-settings.index', ['tab' => 'templates'])">
                        {{ __('email_settings.create.cancel_button') }}
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        {{ __('email_settings.create.submit_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>

    {{-- Make sure Alpine x-cloak hides elements until Alpine is ready. --}}
    <style>[x-cloak] { display: none !important; }</style>
</x-layouts.app>
</content>
</invoke>