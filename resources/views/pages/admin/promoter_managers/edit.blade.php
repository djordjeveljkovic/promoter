<x-layouts.app :title="__('promoter_managers.edit_form.page_title')">
    <div class="space-y-6 max-w-3xl">
        <x-ui.page-header :title="__('promoter_managers.edit_form.main_heading')">
            <x-slot:actions>
                <x-ui.link variant="secondary" :href="route('admin.promoter_managers.index')" icon="arrow-left">
                    {{ __('promoter_managers.edit_form.cancel_button') }}
                </x-ui.link>
            </x-slot:actions>
        </x-ui.page-header>

        @if ($errors->any())
            <x-ui.alert variant="danger" :title="__('promoter_managers.edit_form.errors_title')">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <x-ui.card>
            <form method="POST" action="{{ route('admin.promoter_managers.update', $manager->id) }}" class="space-y-5 p-6">
                @csrf
                @method('PUT')

                <x-ui.field label="{{ __('promoter_managers.edit_form.name_label') }}" for="name" :error="$errors->first('name')" required>
                    <x-ui.input id="name" name="name" :value="old('name', $manager->name)" required />
                </x-ui.field>

                <x-ui.field label="{{ __('promoter_managers.edit_form.email_label') }}" for="email" :error="$errors->first('email')" required>
                    <x-ui.input id="email" name="email" type="email" :value="old('email', $manager->email)" required />
                </x-ui.field>

                <x-ui.field label="{{ __('promoter_managers.edit_form.password_label') }}" for="password"
                            :error="$errors->first('password')"
                            :hint="__('promoter_managers.edit_form.password_help')">
                    <x-ui.input id="password" name="password" type="password" minlength="8" />
                </x-ui.field>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <x-ui.button variant="secondary" :href="route('admin.promoter_managers.index')">
                        {{ __('promoter_managers.edit_form.cancel_button') }}
                    </x-ui.button>
                    <x-ui.button variant="primary" type="submit">
                        {{ __('promoter_managers.edit_form.update_button') }}
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>