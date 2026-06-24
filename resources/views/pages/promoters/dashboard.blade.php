<x-layouts.app :title="__('promoter_dashboard.page_title')">
    <div class="space-y-6">

        <x-ui.page-header :title="__('promoter_dashboard.main_heading')" />

        <section>
            <h2 class="mb-4 text-lg font-semibold text-zinc-700 dark:text-zinc-300">
                {{ __('promoter_dashboard.financial_overview.heading') }}
            </h2>
            <x-ui.kpi-strip :cols="3">
                <x-ui.stat-card
                    :label="__('promoter_dashboard.financial_overview.total_earnings_commission')"
                    :value="number_format($promoterTotalEarnedCommissionAllTime, 2)"
                    :subtext="__('promoter_dashboard.financial_overview.all_time_label')"
                    tone="success"
                    icon="banknotes"
                />
                <x-ui.stat-card
                    :label="__('promoter_dashboard.financial_overview.gross_sales_value')"
                    :value="number_format($promoterGrossSalesAllTime, 2)"
                    :subtext="__('promoter_dashboard.financial_overview.gross_sales_subtext')"
                    icon="chart-bar"
                />
                @if($amountOwedToOrganizersByPromoter >= 0)
                    <x-ui.stat-card
                        :label="__('promoter_dashboard.financial_overview.amount_owed_to_organizers')"
                        :value="number_format($amountOwedToOrganizersByPromoter, 2)"
                        :subtext="__('promoter_dashboard.financial_overview.amount_owed_calculation_subtext')"
                        tone="danger"
                        icon="currency-dollar"
                    />
                @else
                    <x-ui.stat-card
                        :label="__('promoter_dashboard.financial_overview.amount_owed_to_organizers')"
                        :value="'-' . number_format(abs($amountOwedToOrganizersByPromoter), 2)"
                        :subtext="__('promoter_dashboard.financial_overview.organizer_owes_credit_subtext')"
                        tone="success"
                        icon="currency-dollar"
                    />
                @endif
            </x-ui.kpi-strip>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.stat-card
                    :label="__('promoter_dashboard.financial_overview.amount_paid_to_organizers')"
                    :value="number_format($amountAlreadyPaidByPromoter, 2)"
                />
                <x-ui.stat-card
                    :label="__('promoter_dashboard.financial_overview.earnings_last_30_days')"
                    :value="number_format($promoterTotalEarnedCommissionLast30Days, 2)"
                    tone="success"
                    icon="arrow-trending-up"
                />
            </div>
        </section>

        <section>
            <h2 class="mb-4 text-lg font-semibold text-zinc-700 dark:text-zinc-300">
                {{ __('promoter_dashboard.general_performance.heading') }}
            </h2>
            <x-ui.kpi-strip :cols="4">
                <x-ui.stat-card
                    :label="__('promoter_dashboard.general_performance.total_orders_all_time')"
                    :value="number_format($promoterTotalOrdersAllTime)"
                    icon="shopping-bag"
                />
                <x-ui.stat-card
                    :label="__('promoter_dashboard.general_performance.tickets_sold_all_time')"
                    :value="number_format($promoterTotalTicketsSoldAllTime)"
                    icon="ticket"
                />
                <x-ui.stat-card
                    :label="__('promoter_dashboard.general_performance.orders_last_30_days')"
                    :value="number_format($promoterTotalOrdersLast30Days)"
                />
                <x-ui.stat-card
                    :label="__('promoter_dashboard.general_performance.tickets_sold_last_30_days')"
                    :value="number_format($promoterTotalTicketsSoldLast30Days)"
                />
            </x-ui.kpi-strip>
        </section>

        <x-ui.card>
            <x-ui.card.header :title="__('promoter_dashboard.top_ticket_sales_by_type.heading')" />
            @if($promoterTicketTypePerformance->isEmpty())
                <x-ui.empty-state
                    icon="ticket"
                    :title="__('promoter_dashboard.top_ticket_sales_by_type.no_data')"
                />
            @else
                <x-ui.table>
                    <x-ui.table-header>
                        <x-ui.table-row>
                            <x-ui.table-cell header>{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_ticket_type') }}</x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_quantity_sold') }}</x-ui.table-cell>
                            <x-ui.table-cell header align="right" numeric>{{ __('promoter_dashboard.top_ticket_sales_by_type.table_header_gross_revenue') }}</x-ui.table-cell>
                        </x-ui.table-row>
                    </x-ui.table-header>
                    <x-ui.table-body>
                        @foreach($promoterTicketTypePerformance as $type)
                            <x-ui.table-row>
                                <x-ui.table-cell nowrap>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $type->name }}</span>
                                </x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>{{ number_format($type->total_quantity_sold) }}</x-ui.table-cell>
                                <x-ui.table-cell align="right" numeric>{{ number_format($type->total_revenue_generated, 2) }}</x-ui.table-cell>
                            </x-ui.table-row>
                        @endforeach
                    </x-ui.table-body>
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>
</x-layouts.app>
</content>
</invoke>