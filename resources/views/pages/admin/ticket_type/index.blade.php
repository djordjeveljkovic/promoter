<x-layouts.app :title="__('ticket_types.page_title')">
    <div class="space-y-6">
        <x-ui.page-header :title="__('ticket_types.main_heading')">
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('ticket_type.create')" icon="plus">
                    {{ __('ticket_types.create_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('ticket_types.table.header_name') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('ticket_types.table.header_price') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('ticket_types.table.header_photo') }}</x-ui.table-cell>
                        <x-ui.table-cell header>{{ __('ticket_types.table.header_status') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right">{{ __('ticket_types.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($ticketTypes as $ticketType)
                        {{-- Dim the row when deactivated so it's obvious at a glance. --}}
                        <x-ui.table-row @class(['opacity-50' => ! $ticketType->is_active])>
                            <x-ui.table-cell nowrap>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $ticketType->name }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap numeric>
                                {{ number_format($ticketType->price, 2) }} {{ __('ticket_types.currency_symbol') }}
                            </x-ui.table-cell>
                            <x-ui.table-cell nowrap>
                                @if ($ticketType->photo_path)
                                    <img src="{{ asset($ticketType->photo_path) }}" alt="{{ $ticketType->name }}"
                                         class="h-10 w-10 rounded-md object-cover ring-1 ring-zinc-200 dark:ring-zinc-700">
                                @else
                                    <span class="text-xs italic text-zinc-500 dark:text-zinc-400">{{ __('ticket_types.table.no_photo') }}</span>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell>
                                @if ($ticketType->is_active)
                                    <x-ui.badge variant="success">{{ __('ticket_types.table.status_active') }}</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">{{ __('ticket_types.table.status_inactive') }}</x-ui.badge>
                                @endif
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right">
                                <div class="inline-flex items-center gap-2">
                                    <x-ui.link :href="route('ticket_type.edit', $ticketType)" icon="pencil-square">
                                        {{ __('ticket_types.table.action_edit') }}
                                    </x-ui.link>
                                    {{-- Single toggle form. Button label flips based on current state,
                                         and the confirm message is also picked from the lang file
                                         so it matches the action. --}}
                                    <form action="{{ route('ticket_type.toggle_active', $ticketType) }}" method="POST" class="inline"
                                          onsubmit="return confirm('{{ $ticketType->is_active ? __('ticket_types.table.deactivate_confirm_message') : __('ticket_types.table.activate_confirm_message') }}');">
                                        @csrf
                                        @method('PATCH')
                                        @if ($ticketType->is_active)
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-amber-700 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 dark:text-amber-400 dark:hover:bg-amber-500/10 dark:hover:text-amber-300">
                                                {{ __('ticket_types.table.action_deactivate') }}
                                            </button>
                                        @else
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300">
                                                {{ __('ticket_types.table.action_activate') }}
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="5">
                                <x-ui.empty-state
                                    icon="ticket"
                                    :title="__('ticket_types.table.no_data_message')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>

        @if ($ticketTypes->hasPages())
            <div>
                {{ $ticketTypes->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>