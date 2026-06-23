<x-layouts.app :title="__('promoter_managers.sub_promoters.page_title')">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">{{ __('promoter_managers.sub_promoters.main_heading') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('promoter_managers.sub_promoters.sub_heading') }}</p>
            </div>

            <a href="{{ route('promoter_manager.sub_promoters.create') }}"
               class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
                <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                {{ __('promoter_managers.sub_promoters.add_button') }}
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg overflow-hidden">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-100 dark:bg-gray-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ __('promoter_managers.sub_promoters.table.header_name') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.sub_promoters.table.header_orders') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.sub_promoters.table.header_tickets_sold') }}</th>
                            <th scope="col" class="px-6 py-3 text-right">{{ __('promoter_managers.sub_promoters.table.header_commission') }}</th>
                            <th scope="col" class="px-6 py-3 text-center">{{ __('promoter_managers.sub_promoters.table.header_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($subs as $sub)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $sub->name }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-300">{{ $sub->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ $sub->totalOrders }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ $sub->totalTicketsSold }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ number_format($sub->totalCommissionEarned, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <a href="{{ route('promoter_manager.sub_promoters.edit', $sub->id) }}"
                                       class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">
                                        {{ __('promoter_managers.sub_promoters.table.action_edit') }}
                                    </a>
                                    <form action="{{ route('promoter_manager.sub_promoters.destroy', $sub->id) }}" method="POST" class="inline ml-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                                onclick="return confirm('{{ __('promoter_managers.sub_promoters.table.delete_confirm_message') }}')">
                                            {{ __('promoter_managers.sub_promoters.table.action_delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @if(!empty($ticketTypes) && $ticketTypes->count())
                                <tr class="bg-gray-50 dark:bg-gray-700/20">
                                    <td colspan="5" class="px-6 py-3">
                                        <div class="text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase mb-2">{{ __('promoter_managers.sub_promoters.table.commission_per_type_label') }}</div>
                                        <div class="flex flex-wrap gap-3">
                                            @foreach($ticketTypes as $type)
                                                @php $pct = $sub->overridesByType[$type->id] ?? null; @endphp
                                                <div class="px-3 py-1 rounded-md text-xs
                                                    {{ $pct === null
                                                        ? 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
                                                        : 'bg-indigo-50 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-200' }}">
                                                    <span class="font-medium">{{ $type->name }}:</span>
                                                    {{ $pct === null ? __('promoter_managers.sub_promoters.table.no_override') : number_format($pct, 2) . '%' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('promoter_managers.sub_promoters.table.empty_header') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('promoter_managers.sub_promoters.table.empty_message') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
