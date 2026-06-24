<x-layouts.app :title="__('supremeadmin.users.page_title')">
    <div class="space-y-6">
        <x-ui.page-header
            :eyebrow="__('supremeadmin.page_title')"
            :title="__('supremeadmin.users.main_heading')"
            :subtitle="__('supremeadmin.users.sub_heading')"
        />

        {{-- Flash messages --}}
        @if (session('success'))
            <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert variant="danger">{{ session('error') }}</x-ui.alert>
        @endif

        {{-- Filters --}}
        <x-ui.filter-form :action="route('superadmin.users.index')" autosubmit>
            <x-ui.input
                name="search"
                leadingIcon="search"
                :value="$search"
                :placeholder="__('supremeadmin.users.search_placeholder')"
            />

            <x-ui.select name="role" autosubmit :placeholder="__('supremeadmin.users.all_roles_option')">
                @foreach ($allRoles as $r)
                    <option value="{{ $r }}" {{ $roleFilter === $r ? 'selected' : '' }}>
                        {{ __('supremeadmin.users.role_' . $r) }}
                    </option>
                @endforeach
            </x-ui.select>

            <x-ui.button type="submit" variant="primary" icon="search">
                {{ __('supremeadmin.users.search_button') }}
            </x-ui.button>
            <x-ui.button variant="secondary" :href="route('superadmin.users.index')">
                {{ __('supremeadmin.users.clear_button') }}
            </x-ui.button>
        </x-ui.filter-form>

        {{-- Users table --}}
        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('supremeadmin.users.table.header_name') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('supremeadmin.users.table.header_role') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden md:table-cell">{{ __('supremeadmin.users.table.header_parent') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden lg:table-cell" align="right">{{ __('supremeadmin.users.table.header_orders') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden lg:table-cell" align="right">{{ __('supremeadmin.users.table.header_subs') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden sm:table-cell">{{ __('supremeadmin.users.table.header_joined_date') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('supremeadmin.users.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($users as $u)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $u->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $u->email }}</div>
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                <x-ui.badge :variant="match($u->role) {
                                    'supreme', 'superadmin' => 'danger',
                                    'admin' => 'warning',
                                    'promoter' => 'info',
                                    'promoter_manager' => 'indigo',
                                    'sub_promoter' => 'neutral',
                                    default => 'neutral',
                                }" size="sm">
                                    {{ __('supremeadmin.users.role_' . $u->role) }}
                                </x-ui.badge>
                            </x-ui.table-cell>
                            <x-ui.table-cell class="hidden md:table-cell">
                                @if ($u->parent)
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $u->parent->name }}</span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-600">—</span>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell class="hidden lg:table-cell" align="right" numeric>
                                {{ number_format($u->orders_count ?? 0) }}
                            </x-ui.table-cell>
                            <x-ui.table-cell class="hidden lg:table-cell" align="right" numeric>
                                {{ number_format($u->sub_promoters_count ?? 0) }}
                            </x-ui.table-cell>
                            <x-ui.table-cell class="hidden sm:table-cell" nowrap>
                                <span class="text-zinc-600 dark:text-zinc-300">{{ $u->created_at->format('M d, Y') }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                @php
                                    $cannotDelete = $u->id === auth()->id() || $u->isSupreme();
                                @endphp

                                @if ($cannotDelete)
                                    <span class="text-xs text-zinc-400 dark:text-zinc-600 italic">
                                        {{ __('supremeadmin.users.action_locked') }}
                                    </span>
                                @else
                                    <form action="{{ route('superadmin.users.destroy', $u->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                                                onclick="return confirm('{{ __('supremeadmin.users.delete_confirm_message', ['name' => $u->name]) }}')">
                                            <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                            {{ __('supremeadmin.users.action_delete') }}
                                        </button>
                                    </form>
                                @endif
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="7">
                                <x-ui.empty-state
                                    icon="users"
                                    :title="__('supremeadmin.users.empty_results')"
                                    :description="__('supremeadmin.users.empty_results_hint')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>

        @if ($users->hasPages())
            <div>
                {{ $users->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>