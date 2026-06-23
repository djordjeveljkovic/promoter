<x-layouts.app :title="__('promoter_managers.page_title')">
    <div class="space-y-6">
        @if(session('success'))
            <x-ui.alert variant="success" icon="check">{{ session('success') }}</x-ui.alert>
        @endif

        <x-ui.page-header
            :eyebrow="__('navigation.sidebar.promoter_managers')"
            :title="__('promoter_managers.main_heading')"
            :subtitle="__('promoter_managers.sub_heading')"
        >
            <x-slot:actions>
                <x-ui.button variant="primary" :href="route('admin.promoter_managers.create')" icon="plus">
                    {{ __('promoter_managers.add_button') }}
                </x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.card :padding="false">
            <x-ui.table>
                <x-ui.table-header>
                    <x-ui.table-row>
                        <x-ui.table-cell header>{{ __('promoter_managers.table.header_name') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('promoter_managers.table.header_sub_promoters') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden md:table-cell">{{ __('promoter_managers.table.header_gross_sales') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden lg:table-cell">{{ __('promoter_managers.table.header_commission_earned') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden lg:table-cell">{{ __('promoter_managers.table.header_paid_to_organizers') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right">{{ __('promoter_managers.table.header_owed_to_organizers') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="right" class="hidden lg:table-cell">{{ __('promoter_managers.dashboard.quick_stats.team_commission') }}</x-ui.table-cell>
                        <x-ui.table-cell header align="center">{{ __('promoter_managers.table.header_actions') }}</x-ui.table-cell>
                    </x-ui.table-row>
                </x-ui.table-header>
                <x-ui.table-body>
                    @forelse ($managers as $manager)
                        <x-ui.table-row>
                            <x-ui.table-cell>
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                        {{ $manager->initials() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-zinc-900 dark:text-zinc-100">{{ $manager->name }}</p>
                                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $manager->email }}</p>
                                    </div>
                                </div>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                {{ $manager->sub_promoters_count ?? 0 }}
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                {{ number_format($manager->totalGrossSales ?? 0, 2) }} RSD
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden lg:table-cell">
                                {{ number_format($manager->totalCommissionEarned ?? 0, 2) }} RSD
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden lg:table-cell">
                                {{ number_format($manager->amountPaidToOrganizers ?? 0, 2) }} RSD
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric>
                                @php($owed = $manager->amountOwedToOrganizers ?? 0)
                                <span @class([
                                    'inline-flex items-baseline gap-1 font-semibold',
                                    'text-rose-600 dark:text-rose-400' => $owed > 0,
                                    'text-emerald-600 dark:text-emerald-400' => $owed <= 0,
                                ])>
                                    {{ number_format($owed, 2) }} <span class="text-xs text-zinc-500 dark:text-zinc-400">RSD</span>
                                </span>
                            </x-ui.table-cell>
                            <x-ui.table-cell align="right" numeric class="hidden lg:table-cell">
                                {{ number_format(($manager->totalCommissionEarned ?? 0) + ($manager->subCommissionsAllTime ?? 0), 2) }} RSD
                            </x-ui.table-cell>
                            <x-ui.table-cell align="center">
                                <div class="inline-flex items-center gap-1" x-data="{ open: false }">
                                    <x-ui.link :href="route('admin.promoter_managers.edit', $manager->id)" iconTrailing="arrow-right">
                                        {{ __('promoter_managers.table.action_edit') }}
                                    </x-ui.link>
                                    <button type="button"
                                            @click="open = true"
                                            title="{{ __('promoter_managers.table.record_payment_button') }}"
                                            class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 dark:text-emerald-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300">
                                        <x-ui.icon name="currency-dollar" class="h-3.5 w-3.5" />
                                        <span class="hidden xl:inline">{{ __('promoter_managers.table.record_payment_button') }}</span>
                                    </button>
                                    <form action="{{ route('admin.promoter_managers.destroy', $manager->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:bg-rose-500/10 dark:hover:text-rose-300"
                                                onclick="return confirm('{{ __('promoter_managers.table.delete_confirm_message') }}')">
                                            <x-ui.icon name="trash" class="h-3.5 w-3.5" />
                                        </button>
                                    </form>

                                    {{-- Quick modal: record a payment from this manager to the organizers --}}
                                    <div x-show="open"
                                         x-cloak
                                         x-transition.opacity
                                         @keydown.escape.window="open = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center p-4"
                                         style="display: none;">
                                        {{-- Backdrop --}}
                                        <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm"
                                             @click="open = false"></div>

                                        {{-- Dialog --}}
                                        <div @click.outside="open = false"
                                             x-transition
                                             class="relative z-10 w-full max-w-md rounded-xl bg-white shadow-xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
                                            <form method="POST"
                                                  action="{{ route('admin.payments.from_manager.store', $manager->id) }}"
                                                  class="space-y-4 p-6">
                                                @csrf
                                                <input type="hidden" name="redirect_to" value="manager_index" />

                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                                            {{ __('promoter_managers.table.record_payment_modal_title') }}
                                                        </h3>
                                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                                            {{ $manager->name }}
                                                        </p>
                                                    </div>
                                                    <button type="button" @click="open = false"
                                                            class="rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
                                                        <x-ui.icon name="x-mark" class="h-5 w-5" />
                                                        <span class="sr-only">{{ __('promoter_managers.table.record_payment_cancel') }}</span>
                                                    </button>
                                                </div>

                                                <p class="rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-300">
                                                    {{ __('promoter_managers.table.record_payment_helper') }}
                                                </p>

                                                <x-ui.field label="{{ __('promoter_managers.table.record_payment_amount_label') }}" for="amount-{{ $manager->id }}" required>
                                                    <x-ui.input id="amount-{{ $manager->id }}"
                                                                name="amount"
                                                                type="number"
                                                                step="0.01"
                                                                min="0.01"
                                                                max="9999999.99"
                                                                :value="number_format((float) ($manager->amountOwedToOrganizers ?? 0), 2, '.', '')"
                                                                required />
                                                </x-ui.field>

                                                <x-ui.field label="{{ __('promoter_managers.table.record_payment_note_label') }}" for="note-{{ $manager->id }}">
                                                    <x-ui.textarea id="note-{{ $manager->id }}" name="note" rows="2" />
                                                </x-ui.field>

                                                <div class="flex items-center justify-end gap-2 pt-2">
                                                    <x-ui.button variant="secondary" type="button" @click="open = false">
                                                        {{ __('promoter_managers.table.record_payment_cancel') }}
                                                    </x-ui.button>
                                                    <x-ui.button variant="success" type="submit" icon="check">
                                                        {{ __('promoter_managers.table.record_payment_submit') }}
                                                    </x-ui.button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @empty
                        <x-ui.table-row :hover="false">
                            <x-ui.table-cell colspan="8">
                                <x-ui.empty-state
                                    icon="users"
                                    :title="__('promoter_managers.table.no_managers_header')"
                                    :description="__('promoter_managers.table.no_managers_message')"
                                />
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    @endforelse
                </x-ui.table-body>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-layouts.app>