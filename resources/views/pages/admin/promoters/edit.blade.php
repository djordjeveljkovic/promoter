<x-layouts.app :title="__('promoters.edit_form.page_title')">
    <div class="space-y-6 max-w-3xl">
        <x-ui.page-header :title="__('promoters.edit_form.main_heading')">
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('admin.promoters.index')" icon="arrow-left">
                    {{ __('promoters.edit_form.cancel_button') }}
                </x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($errors->any())
            <x-ui.alert variant="danger" :title="__('promoters.edit_form.errors_title')">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.promoters.update', $promoter->id) }}" class="space-y-5 p-6">
                @csrf
                @method('PUT')

                <x-ui.field label="{{ __('promoters.edit_form.name_label') }}" for="name" :error="$errors->first('name')" required>
                    <x-ui.input id="name" name="name" :value="old('name', $promoter->name)"
                                placeholder="{{ __('promoters.edit_form.name_placeholder') }}" required />
                </x-ui.field>

                <x-ui.field label="{{ __('promoters.edit_form.email_label') }}" for="email" :error="$errors->first('email')" required>
                    <x-ui.input id="email" name="email" type="email" :value="old('email', $promoter->email)"
                                placeholder="you@example.com" required />
                </x-ui.field>

                <x-ui.field label="{{ __('promoters.edit_form.password_label') }}" for="password"
                            :error="$errors->first('password')"
                            :hint="__('promoters.edit_form.password_help_text')">
                    <x-ui.input id="password" name="password" type="password"
                                placeholder="{{ __('promoters.edit_form.password_placeholder_edit') }}" />
                </x-ui.field>

                <x-ui.field label="{{ __('promoters.edit_form.paid_label') }}" for="paid" :error="$errors->first('paid')" required>
                    <x-ui.input id="paid" name="paid" :value="old('paid', $promoter->paid)"
                                placeholder="{{ __('promoters.edit_form.paid_placeholder') }}" required />
                </x-ui.field>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-ui.button variant="secondary" :href="route('admin.promoters.index')">
                        {{ __('promoters.edit_form.cancel_button') }}
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        {{ __('promoters.edit_form.update_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>