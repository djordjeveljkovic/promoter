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
            ? 'text-rose-600 dark:text-rose-400 font-semibold'
            : 'text-emerald-600 dark:text-emerald-400 font-semibold';
    @endphp

    <div class="space-y-6"
         x-data="supremeOverview()">

        {{-- ============================================================ --}}
        {{-- Header                                                        --}}
        {{-- ============================================================ --}}
        <x-ui.page-header
            :title="__('supremeadmin.main_heading')"
            :subtitle="__('supremeadmin.sub_heading')"
        >
            <x-slot:actions>
                <form method="GET" action="{{ route('supremeadmin.overview') }}" class="flex gap-2">
                    <x-ui.input name="search" type="search" :value="$search"
                                :placeholder="__('supremeadmin.search_placeholder')"
                                class="min-w-[12rem]" />
                    <x-ui.button type="submit" variant="primary" icon="search">
                        {{ __('supremeadmin.search_button') }}
                    </x-ui.button>
                    @if ($search !== '')
                        <x-ui.button variant="secondary" :href="route('supremeadmin.overview')">
                            {{ __('supremeadmin.clear_button') }}
                        </x-ui.button>
                    @endif
                </form>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- ============================================================ --}}
        {{-- Summary KPIs                                                  --}}
        {{-- ============================================================ --}}
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
            $owedLabel = __('supremeadmin.kpi.owed');
        @endphp
        <x-ui.kpi-strip :cols="7">
            @foreach ($kpis as $kpi)
                @php $isOwed = $kpi['label'] === $owedLabel; @endphp
                <div class="flex flex-col gap-1 bg-white p-4 dark:bg-zinc-900 sm:p-5">
                    <p class="text-[11px] font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ $kpi['label'] }}
                    </p>
                    <div @class([
                        'mt-1 text-xl font-bold',
                        $owedClass($kpi['value']) => $isOwed,
                        'text-zinc-900 dark:text-white' => ! $isOwed,
                    ])>
                        {{ $kpi['format'] === 'money' ? $fmt($kpi['value']) : number_format($kpi['value']) }}
                    </div>
                </div>
            @endforeach
        </x-ui.kpi-strip>

        {{-- ============================================================ --}}
        {{-- Filters                                                       --}}
        {{-- ============================================================ --}}
        <x-ui.card>
            <form method="GET" action="{{ route('supremeadmin.overview') }}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 pt-4">
                    <h2 class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">
                        {{ __('supremeadmin.filters.heading') }}
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="addFilter()"
                                class="inline-flex items-center justify-center gap-2 font-semibold rounded-lg shadow-sm ring-1 ring-inset ring-zinc-700 bg-zinc-700 hover:bg-zinc-800 text-white px-3 py-1.5 text-xs transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-zinc-500">
                            {{ __('supremeadmin.filters.add_button') }}
                        </button>
                        <x-ui.button type="submit" variant="primary" size="sm">
                            {{ __('supremeadmin.filters.apply_button') }}
                        </x-ui.button>
                        @if (count($filters) > 0)
                            <x-ui.button variant="secondary" size="sm"
                                        :href="route('supremeadmin.overview', ['search' => $search])">
                                {{ __('supremeadmin.filters.reset_button') }}
                            </x-ui.button>
                        @endif
                    </div>
                </div>

                {{-- Preserve search and sort through the filter submit --}}
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort"    value="{{ $sortField }}_{{ $sortDir }}">

                <div class="space-y-2 p-4">
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
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300" x-text="fieldLabel"></label>
                                <select :name="`filter[${baseCount + idx}][field]`" x-model="row.field"
                                        class="mt-1 block w-full appearance-none rounded-lg border border-zinc-300 bg-white pl-3 pr-9 py-1.5 text-xs text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                    <template x-for="(label, value) in fieldOptions" :key="value">
                                        <option :value="value" x-text="label" :selected="row.field === value"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300" x-text="opLabel"></label>
                                <select :name="`filter[${baseCount + idx}][op]`" x-model="row.op"
                                        class="mt-1 block w-full appearance-none rounded-lg border border-zinc-300 bg-white pl-3 pr-9 py-1.5 text-xs text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                    <template x-for="(label, value) in opOptions" :key="value">
                                        <option :value="value" x-text="label" :selected="row.op === value"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300" x-text="amountLabel"></label>
                                <input type="number" step="0.01" min="0" :name="`filter[${baseCount + idx}][amount]`" x-model="row.amount"
                                       class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-xs text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300" x-text="scopeLabel"></label>
                                <select :name="`filter[${baseCount + idx}][scope]`" x-model="row.scope"
                                        class="mt-1 block w-full appearance-none rounded-lg border border-zinc-300 bg-white pl-3 pr-9 py-1.5 text-xs text-zinc-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100">
                                    <template x-for="(label, value) in scopeOptions" :key="value">
                                        <option :value="value" x-text="label" :selected="row.scope === value"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="sm:col-span-1">
                                <button type="button" @click="removeFilter(idx)"
                                        class="w-full inline-flex items-center justify-center px-2 py-2 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-300 hover:bg-rose-200 dark:hover:bg-rose-900/60 text-xs font-medium">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- If neither persisted nor new filters are present, show a hint --}}
                    @if (count($filters) === 0)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400" x-show="rows.length === 0">
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
        </x-ui.card>

        {{-- ============================================================ --}}
        {{-- Results                                                       --}}
        {{-- ============================================================ --}}
        @if ($managers->isEmpty())
            <x-ui.card>
                <x-ui.empty-state icon="users" title="{{ __('supremeadmin.empty_results') }}" />
            </x-ui.card>
        @else
            <x-ui.card :padding="false">
                <x-ui.table>
                    <x-ui.table-header>
                        <x-ui.table-row>
                            <x-ui.table-cell header>
                                <a href="{{ $sortUrl('name', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_manager') }} {{ $sortIcon('name', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell header align="right">
                                <a href="{{ $sortUrl('subs', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_subs') }} {{ $sortIcon('subs', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>
                                <a href="{{ $sortUrl('gross_sales', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_gross_sales') }} {{ $sortIcon('gross_sales', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>
                                <a href="{{ $sortUrl('commission_earned', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_commission_earned') }} {{ $sortIcon('commission_earned', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>
                                <a href="{{ $sortUrl('paid', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_paid') }} {{ $sortIcon('paid', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>
                                <a href="{{ $sortUrl('owed', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_owed') }} {{ $sortIcon('owed', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric class="hidden md:table-cell">
                                <a href="{{ $sortUrl('tickets', $sortField, $sortDir) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ __('supremeadmin.table.header_tickets') }} {{ $sortIcon('tickets', $sortField, $sortDir) }}
                                </a>
                            </x-ui.table-cell>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @foreach ($managers as $manager)
                            <x-ui.table-row onclick="document.getElementById('subs-{{ $manager->id }}').classList.toggle('hidden')">
                                <x-ui.table-cell>
                                    <div class="font-medium text-zinc-900 dark:text-white">{{ $manager->name }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $manager->email }}</div>
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>
                                    {{ $manager->subPromoters->count() }}
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>
                                    {{ $fmt($manager->team_gross_sales) }}
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>
                                    <span class="font-semibold text-zinc-900 dark:text-white">{{ $fmt($manager->team_commission_earned) }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>
                                    {{ $fmt($manager->team_paid) }}
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>
                                    <span class="{{ $owedClass($manager->team_owed) }}">{{ $fmt($manager->team_owed) }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric class="hidden md:table-cell">
                                    {{ number_format($manager->team_tickets) }}
                                </x-ui.table-cell>
                            </x-ui.table-row>

                            {{-- Collapsible sub-promoter detail row --}}
                            <x-ui.table-row :hover="false" id="subs-{{ $manager->id }}" class="hidden bg-zinc-50 dark:bg-zinc-900/40">
                                <x-ui.table-cell colspan="7" class="!px-4 !py-3">
                                    @if ($manager->subPromoters->isEmpty())
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 italic">
                                            {{ __('supremeadmin.sub.empty') }}
                                        </p>
                                    @else
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full text-xs">
                                                <thead>
                                                    <tr class="text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                                        <th class="px-3 py-2 text-left">{{ __('supremeadmin.sub.header_name') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_orders') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_tickets') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_gross_sales') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_commission') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_paid') }}</th>
                                                        <th class="px-3 py-2 text-right">{{ __('supremeadmin.sub.header_owed') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                                    @foreach ($manager->subRows as $subRow)
                                                        <tr class="hover:bg-zinc-100 dark:hover:bg-zinc-800/60">
                                                            <td class="px-3 py-2">
                                                                <div class="font-medium text-zinc-900 dark:text-white">{{ $subRow->user->name }}</div>
                                                                <div class="text-zinc-500 dark:text-zinc-400">{{ $subRow->user->email }}</div>
                                                            </td>
                                                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($subRow->orders_count) }}</td>
                                                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($subRow->tickets_sold) }}</td>
                                                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ $fmt($subRow->gross_sales) }}</td>
                                                            <td class="px-3 py-2 text-right font-semibold text-zinc-900 dark:text-white">{{ $fmt($subRow->commission_earned) }}</td>
                                                            <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ $fmt($subRow->paid) }}</td>
                                                            <td class="px-3 py-2 text-right {{ $owedClass($subRow->owed) }}">{{ $fmt($subRow->owed) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    {{-- Manager's own row in the team breakdown --}}
                                                    <tr class="bg-white dark:bg-zinc-800/40 font-medium">
                                                        <td class="px-3 py-2 text-zinc-900 dark:text-white">
                                                            <span class="italic">{{ __('supremeadmin.sub.manager_self_label') }}</span>
                                                            — {{ $manager->name }}
                                                        </td>
                                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($manager->self->orders_count) }}</td>
                                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ number_format($manager->self->tickets_sold) }}</td>
                                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ $fmt($manager->self->gross_sales) }}</td>
                                                        <td class="px-3 py-2 text-right text-zinc-900 dark:text-white">{{ $fmt($manager->self->commission_earned) }}</td>
                                                        <td class="px-3 py-2 text-right text-zinc-700 dark:text-zinc-300">{{ $fmt($manager->self->paid) }}</td>
                                                        <td class="px-3 py-2 text-right {{ $owedClass($manager->self->owed) }}">{{ $fmt($manager->self->owed) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </x-ui.table-cell>
                            </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
            </x-ui.card>
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
</content>
</invoke>