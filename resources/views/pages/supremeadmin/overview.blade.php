<x-layouts.app :title="__('supremeadmin.page_title')">
    @php
        // Helpers used inside the template — small so we just inline them.
        $fmt = fn ($v) => number_format((float) $v, 2);
        $sortUrl = function (string $field, string $currentField, string $currentDir) {
            $newDir = ($field === $currentField && $currentDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $field . '_' . $newDir]);
        };
        $sortIcon = function (string $field, string $currentField, string $currentDir) {
            if ($field !== $currentField) return '';
            return $currentDir === 'asc' ? '▲' : '▼';
        };
        $owedClass = fn ($v) => $v > 0
            ? 'text-red-600 dark:text-red-400 font-semibold'
            : 'text-green-600 dark:text-green-400 font-semibold';
    @endphp

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="supremeOverview()">

        {{-- ============================================================ --}}
        {{-- Header                                                        --}}
        {{-- ============================================================ --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white">
                    {{ __('supremeadmin.main_heading') }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('supremeadmin.sub_heading') }}
                </p>
            </div>

            <form method="GET" action="{{ route('supremeadmin.overview') }}" class="flex gap-2">
                <input type="search" name="search" value="{{ $search }}"
                       placeholder="{{ __('supremeadmin.search_placeholder') }}"
                       class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <button type="submit"
                        class="inline-flex items-center px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700">
                    {{ __('supremeadmin.search_button') }}
                </button>
                @if ($search !== '')
                    <a href="{{ route('supremeadmin.overview') }}"
                       class="inline-flex items-center px-3 py-2 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-600">
                        {{ __('supremeadmin.clear_button') }}
                    </a>
                @endif
            </form>
        </div>

        {{-- ============================================================ --}}
        {{-- Summary KPIs                                                  --}}
        {{-- ============================================================ --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
            @php
                $kpis = [
                    ['label' => __('supremeadmin.kpi.managers'),    'value' => $summary['managers_count'],    'format' => 'int'],
                    ['label' => __('supremeadmin.kpi.subs'),        'value' => $summary['subs_count'],        'format' => 'int'],
                    ['label' => __('supremeadmin.kpi.tickets'),     'value' => $summary['tickets_total'],     'format' => 'int'],
                    ['label' => __('supremeadmin.kpi.gross_sales'), 'value' => $summary['gross_sales_total'], 'format' => 'money'],
                    ['label' => __('supremeadmin.kpi.commission'),  'value' => $summary['commission_total'],  'format' => 'money'],
                    ['label' => __('supremeadmin.kpi.paid'),        'value' => $summary['paid_total'],        'format' => 'money'],
                    ['label' => __('supremeadmin.kpi.owed'),        'value' => $summary['owed_total'],        'format' => 'money'],
                ];
            @endphp
            @foreach ($kpis as $kpi)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $kpi['label'] }}</div>
                    <div class="mt-1 text-xl font-bold {{ $kpi['label'] === __('supremeadmin.kpi.owed') ? $owedClass($kpi['value']) : 'text-gray-900 dark:text-white' }}">
                        {{ $kpi['format'] === 'money' ? $fmt($kpi['value']) : number_format($kpi['value']) }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ============================================================ --}}
        {{-- Filters                                                       --}}
        {{-- ============================================================ --}}
        <form method="GET" action="{{ route('supremeadmin.overview') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
                    {{ __('supremeadmin.filters.heading') }}
                </h2>
                <div class="flex gap-2">
                    <button type="button" @click="addFilter()"
                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-gray-700 dark:bg-gray-600 hover:bg-gray-800 text-white text-xs font-medium">
                        {{ __('supremeadmin.filters.add_button') }}
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium">
                        {{ __('supremeadmin.filters.apply_button') }}
                    </button>
                    @if (count($filters) > 0)
                        <a href="{{ route('supremeadmin.overview', ['search' => $search]) }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 text-xs font-medium">
                            {{ __('supremeadmin.filters.reset_button') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Preserve search and sort through the filter submit --}}
            <input type="hidden" name="search" value="{{ $search }}">
            <input type="hidden" name="sort"    value="{{ $sortField }}_{{ $sortDir }}">

            <div class="space-y-2">
                @php
                    $fieldLabels = [
                        'gross_sales'       => __('supremeadmin.fields.gross_sales'),
                        'commission_earned' => __('supremeadmin.fields.commission_earned'),
                        'paid'              => __('supremeadmin.fields.paid'),
                        'owed'              => __('supremeadmin.fields.owed'),
                    ];
                    $opLabels = [
                        '>=' => '≥',
                        '<=' => '≤',
                        '='  => '=',
                        '>'  => '>',
                        '<'  => '<',
                    ];
                    $scopeLabels = [
                        'team' => __('supremeadmin.scope.team'),
                        'self' => __('supremeadmin.scope.self'),
                    ];
                @endphp

                {{-- We render the persisted filters as hidden inputs (so the
                     URL stays the source of truth) and provide an Alpine
                     template row for adding more. --}}
                @foreach ($filters as $i => $f)
                    <input type="hidden" name="filter[{{ $i }}][field]"  value="{{ $f['field'] }}">
                    <input type="hidden" name="filter[{{ $i }}][op]"    value="{{ $f['op'] }}">
                    <input type="hidden" name="filter[{{ $i }}][amount]" value="{{ $f['amount'] }}">
                    <input type="hidden" name="filter[{{ $i }}][scope]"  value="{{ $f['scope'] }}">
                @endforeach

                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-end">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300" x-text="fieldLabel"></label>
                            <select :name="`filter[${baseCount + idx}][field]`" x-model="row.field"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <template x-for="(label, value) in fieldOptions" :key="value">
                                    <option :value="value" x-text="label" :selected="row.field === value"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300" x-text="opLabel"></label>
                            <select :name="`filter[${baseCount + idx}][op]`" x-model="row.op"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <template x-for="(label, value) in opOptions" :key="value">
                                    <option :value="value" x-text="label" :selected="row.op === value"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300" x-text="amountLabel"></label>
                            <input type="number" step="0.01" min="0" :name="`filter[${baseCount + idx}][amount]`" x-model="row.amount"
                                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300" x-text="scopeLabel"></label>
                            <select :name="`filter[${baseCount + idx}][scope]`" x-model="row.scope"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <template x-for="(label, value) in scopeOptions" :key="value">
                                    <option :value="value" x-text="label" :selected="row.scope === value"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-1">
                            <button type="button" @click="removeFilter(idx)"
                                    class="w-full inline-flex items-center justify-center px-2 py-2 rounded-md bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/60 text-xs font-medium">
                                ✕
                            </button>
                        </div>
                    </div>
                </template>

                {{-- If neither persisted nor new filters are present, show a hint --}}
                @if (count($filters) === 0)
                    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="rows.length === 0">
                        {{ __('supremeadmin.filters.empty_hint') }}
                    </p>
                @endif

                {{-- Visual summary of persisted filters --}}
                @if (count($filters) > 0)
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($filters as $f)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-800 dark:text-indigo-200">
                                <span class="font-semibold">{{ $scopeLabels[$f['scope']] ?? $f['scope'] }}</span>
                                ·
                                <span>{{ $fieldLabels[$f['field']] ?? $f['field'] }}</span>
                                <span>{{ $opLabels[$f['op']] ?? $f['op'] }}</span>
                                <span>{{ $fmt($f['amount']) }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </form>

        {{-- ============================================================ --}}
        {{-- Results                                                       --}}
        {{-- ============================================================ --}}
        @if ($managers->isEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">{{ __('supremeadmin.empty_results') }}</p>
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-300">
                                    <a href="{{ $sortUrl('name', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_manager') }} {{ $sortIcon('name', $sortField, $sortDir) }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <a href="{{ $sortUrl('subs', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_subs') }} {{ $sortIcon('subs', $sortField, $sortDir) }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <a href="{{ $sortUrl('gross_sales', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_gross_sales') }} {{ $sortIcon('gross_sales', $sortField, $sortDir) }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <a href="{{ $sortUrl('commission_earned', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_commission_earned') }} {{ $sortIcon('commission_earned', $sortField, $sortDir) }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <a href="{{ $sortUrl('paid', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_paid') }} {{ $sortIcon('paid', $sortField, $sortDir) }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                    <a href="{{ $sortUrl('owed', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_owed') }} {{ $sortIcon('owed', $sortField, $sortDir) }}
                                    </a>
                                </th>
                                <th class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300 hidden md:table-cell">
                                    <a href="{{ $sortUrl('tickets', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ __('supremeadmin.table.header_tickets') }} {{ $sortIcon('tickets', $sortField, $sortDir) }}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($managers as $manager)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 cursor-pointer"
                                    onclick="document.getElementById('subs-{{ $manager->id }}').classList.toggle('hidden')">
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $manager->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $manager->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">
                                        {{ $manager->subPromoters->count() }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">
                                        {{ $fmt($manager->team_gross_sales) }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right font-semibold text-gray-900 dark:text-white">
                                        {{ $fmt($manager->team_commission_earned) }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300">
                                        {{ $fmt($manager->team_paid) }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right {{ $owedClass($manager->team_owed) }}">
                                        {{ $fmt($manager->team_owed) }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-right text-gray-700 dark:text-gray-300 hidden md:table-cell">
                                        {{ number_format($manager->team_tickets) }}
                                    </td>
                                </tr>

                                {{-- Collapsible sub-promoter detail row --}}
                                <tr id="subs-{{ $manager->id }}" class="hidden bg-gray-50 dark:bg-gray-900/40">
                                    <td colspan="7" class="px-4 py-3">
                                        @if ($manager->subPromoters->isEmpty())
                                            <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                                                {{ __('supremeadmin.sub.empty') }}
                                            </p>
                                        @else
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full text-xs">
                                                    <thead>
                                                        <tr class="text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                            <th class="px-3 py-2 text-left">{{ __('supremeadmin.sub.header_name') }}</th>
                                                            <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_orders') }}</th>
                                                            <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_tickets') }}</th>
                                                            <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_gross_sales') }}</th>
                                                            <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_commission') }}</th>
                                                            <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_paid') }}</th>
                                                            <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_owed') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                        @foreach ($manager->subRows as $subRow)
                                                            <tr class="hover:bg-gray-100 dark:hover:bg-gray-800/60">
                                                                <td class="px-3 py-2">
                                                                    <div class="font-medium text-gray-900 dark:text-white">{{ $subRow->user->name }}</div>
                                                                    <div class="text-gray-500 dark:text-gray-400">{{ $subRow->user->email }}</div>
                                                                </td>
                                                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($subRow->orders_count) }}</td>
                                                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($subRow->tickets_sold) }}</td>
                                                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ $fmt($subRow->gross_sales) }}</td>
                                                                <td class="px-3 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ $fmt($subRow->commission_earned) }}</td>
                                                                <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ $fmt($subRow->paid) }}</td>
                                                                <td class="px-3 py-2 text-right {{ $owedClass($subRow->owed) }}">{{ $fmt($subRow->owed) }}</td>
                                                            </tr>
                                                        @endforeach
                                                        {{-- Manager's own row in the team breakdown --}}
                                                        <tr class="bg-white dark:bg-gray-800/40 font-medium">
                                                            <td class="px-3 py-2 text-gray-900 dark:text-white">
                                                                <span class="italic">{{ __('supremeadmin.sub.manager_self_label') }}</span>
                                                                — {{ $manager->name }}
                                                            </td>
                                                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($manager->self->orders_count) }}</td>
                                                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($manager->self->tickets_sold) }}</td>
                                                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ $fmt($manager->self->gross_sales) }}</td>
                                                            <td class="px-3 py-2 text-right text-gray-900 dark:text-white">{{ $fmt($manager->self->commission_earned) }}</td>
                                                            <td class="px-3 py-2 text-right text-gray-700 dark:text-gray-300">{{ $fmt($manager->self->paid) }}</td>
                                                            <td class="px-3 py-2 text-right {{ $owedClass($manager->self->owed) }}">{{ $fmt($manager->self->owed) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <style>[x-cloak] { display: none !important; }</style>
    </div>

    <script>
        function supremeOverview() {
            return {
                baseCount: {{ count($filters) }},
                rows: [],
                fieldLabel:  @js(__('supremeadmin.filters.field_label')),
                opLabel:     @js(__('supremeadmin.filters.op_label')),
                amountLabel: @js(__('supremeadmin.filters.amount_label')),
                scopeLabel:  @js(__('supremeadmin.filters.scope_label')),
                fieldOptions:  @js($fieldLabels),
                opOptions:     @js($opLabels),
                scopeOptions:  @js($scopeLabels),
                addFilter() {
                    this.rows.push({ field: 'commission_earned', op: '>=', amount: '', scope: 'team' });
                },
                removeFilter(idx) {
                    this.rows.splice(idx, 1);
                },
            };
        }
        window.supremeOverview = supremeOverview;
    </script>
</x-layouts.app>