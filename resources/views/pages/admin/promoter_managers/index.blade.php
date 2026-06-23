<x-layouts.app :title="__('promoter_managers.page_title')">
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-200">
                    <div class="flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-5" />
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <header class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                        {{ __('navigation.sidebar.promoter_managers') }}
                    </p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                        {{ __('promoter_managers.main_heading') }}
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                        {{ __('promoter_managers.sub_heading') }}
                    </p>
                </div>
                <a href="{{ route('admin.promoter_managers.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
                    <flux:icon name="plus" class="size-4" />
                    {{ __('promoter_managers.add_button') }}
                </a>
            </header>

            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-zinc-900 dark:ring-zinc-800">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-800">
                        <thead class="bg-gray-50 dark:bg-zinc-900/50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.table.header_name') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.table.header_sub_promoters') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.table.header_gross_sales') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.table.header_commission_earned') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.table.header_paid_to_organizers') }}
                                </th>
                                <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.table.header_owed_to_organizers') }}
                                </th>
                                <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.dashboard.quick_stats.team_commission') }}
                                </th>
                                <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ __('promoter_managers.dashboard.my_subs.heading') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                            @forelse ($managers as $manager)
                                <tr class="transition hover:bg-gray-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex size-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">
                                                {{ $manager->initials() }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $manager->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $manager->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-900 dark:text-white">
                                        {{ $manager->sub_promoters_count ?? 0 }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-900 dark:text-white">
                                        {{ number_format($manager->totalGrossSales ?? 0, 2) }} RSD
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-900 dark:text-white">
                                        {{ number_format($manager->totalCommissionEarned ?? 0, 2) }} RSD
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm text-gray-900 dark:text-white">
                                        {{ number_format($manager->amountPaidToOrganizers ?? 0, 2) }} RSD
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right">
                                        <span class="inline-flex items-baseline gap-1 text-sm font-semibold {{ ($manager->amountOwedToOrganizers ?? 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                            {{ number_format($manager->amountOwedToOrganizers ?? 0, 2) }} <span class="text-xs text-gray-500">RSD</span>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm text-gray-900 dark:text-white">
                                        {{ number_format(($manager->totalCommissionEarned ?? 0) + ($manager->subCommissionsAllTime ?? 0), 2) }} RSD
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm">
                                        <a href="{{ route('admin.promoter_managers.edit', $manager->id) }}"
                                           class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 font-medium text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10">
                                            {{ __('promoter_managers.table.action_edit') }}
                                            <flux:icon name="arrow-right" class="size-3.5" />
                                        </a>
                                        <form action="{{ route('admin.promoter_managers.destroy', $manager->id) }}" method="POST" class="inline ml-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 font-medium text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                                    onclick="return confirm('{{ __('promoter_managers.table.delete_confirm_message') }}')">
                                                <flux:icon name="trash" class="size-3.5" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('promoter_managers.table.no_managers_header') }}</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('promoter_managers.table.no_managers_message') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
