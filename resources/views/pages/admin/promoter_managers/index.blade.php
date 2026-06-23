<x-layouts.app :title="__('promoter_managers.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ __('promoter_managers.main_heading') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('promoter_managers.sub_heading') }}</p>
            </div>

            <a href="{{ route('admin.promoter_managers.create') }}"
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('promoter_managers.add_button') }}
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-100 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('promoter_managers.table.header_name') }}</th>
                            <th scope="col" class="px-6 py-3">{{ __('promoter_managers.table.header_sub_promoters') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.table.header_gross_sales') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.table.header_commission_earned') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.table.header_paid_to_organizers') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.table.header_owed_to_organizers') }}</th>
                            <th scope="col" class="px-6 py-3 text-center">{{ __('promoter_managers.table.header_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($managers as $manager)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $manager->name }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300">{{ $manager->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ $manager->sub_promoters_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ number_format($manager->totalGrossSales ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ number_format($manager->totalCommissionEarned ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ number_format($manager->amountPaidToOrganizers ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <div class="text-sm font-semibold {{ ($manager->amountOwedToOrganizers ?? 0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        {{ number_format($manager->amountOwedToOrganizers ?? 0, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('admin.promoter_managers.edit', $manager->id) }}"
                                       class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">{{ __('promoter_managers.table.action_edit') }}</a>
                                    <form action="{{ route('admin.promoter_managers.destroy', $manager->id) }}" method="POST" class="inline ml-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                                onclick="return confirm('{{ __('promoter_managers.table.delete_confirm_message') }}')">{{ __('promoter_managers.table.action_delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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
</x-layouts.app>
