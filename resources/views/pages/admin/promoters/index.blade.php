<x-layouts.app :title="__('promoters.page_title')">
    <div class="space-y-6">
        <x-ui.page-header :title="__('promoters.main_heading')">
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('admin.promoters.create')" icon="plus">
                    {{ __('promoters.add_promoter_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('promoters.table.header_name') }}</x-ui.table-cell>
                        <x-ui.table-cell header class="hidden sm:table-cell">{{ __('promoters.table.header_joined_date') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('promoters.table.header_tickets_sold') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('promoters.table.header_made_for_organizers') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('promoters.table.header_commission_earned') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('promoters.table.header_paid_to_organizers') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right">{{ __('promoters.table.header_owed_to_organizers') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('promoters.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($promoters as $promoter)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $promoter->name }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $promoter->email }}</div>
                            </x-ui.table-cell>
                            <x-ui.table-cell class="hidden sm:table-cell" nowrap>
                                <span class="text-zinc-600 dark:text-zinc-300">{{ $promoter->created_at->format('M d, Y') }}</span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                {{ $promoter->ticketsSoldCount ?? 0 }}
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                {{ number_format($promoter->madeForOrganizers ?? 0.00, 2) }}
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                {{ number_format($promoter->totalCommissionEarned ?? 0.00, 2) }}
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                {{ number_format($promoter->amountPaidToOrganizers ?? 0.00, 2) }}
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>
                                @php($owed = $promoter->amountOwedToOrganizers ?? 0.00)
                                <span @class([
                                    'font-semibold',
                                    'text-rose-600 dark:text-rose-400' => $owed > 0,
                                    'text-emerald-600 dark:text-emerald-400' => $owed <= 0,
                                ])>
                                    {{ number_format($owed, 2) }}
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                <div class="inline-flex items-center gap-2">
                                    <x-ui.link :href="route('admin.promoters.edit', $promoter->id)" icon="pencil-square">
                                        {{ __('promoters.table.action_edit') }}
                                    </x-ui.link>
                                    <form action="{{ route('admin.promoters.destroy', $promoter->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                                                onclick="return confirm('{{ __('promoters.table.delete_confirm_message') }}')">
                                            <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                            {{ __('promoters.table.action_delete') }}
                                        </button>
                                    </form>
                                </div>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="8">
                                <x-ui.empty-state
                                    icon="users"
                                    :title="__('promoters.table.no_promoters_header')"
                                    :description="__('promoters.table.no_promoters_message')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>

        @if (method_exists($promoters, 'hasPages') && $promoters->hasPages())
            <div>
                {{ $promoters->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>