<?php

return [
    'page_title'         => 'Supreme Admin Overview',
    'main_heading'       => 'Supreme Admin Overview',
    'sub_heading'        => 'Bird\'s-eye view of every promoter-manager, their sub-promoters, and how much each one has earned or owes the festival.',
    'search_placeholder' => 'Search by name or email…',
    'search_button'      => 'Search',
    'clear_button'       => 'Clear',
    'empty_results'      => 'No promoter-managers match the current filters.',

    'kpi' => [
        'managers'    => 'Managers',
        'subs'        => 'Sub-promoters',
        'tickets'     => 'Tickets sold',
        'gross_sales' => 'Gross sales',
        'commission'  => 'Commission earned',
        'paid'        => 'Paid to festival',
        'owed'        => 'Owed to festival',
    ],

    'fields' => [
        'gross_sales'       => 'Gross sales',
        'commission_earned' => 'Commission earned',
        'paid'              => 'Paid to festival',
        'owed'              => 'Owed to festival',
    ],

    'scope' => [
        'team' => 'Whole team',
        'self' => 'Only this user',
    ],

    'filters' => [
        'heading'       => 'Filters',
        'field_label'   => 'Field',
        'op_label'      => 'Operator',
        'amount_label'  => 'Amount',
        'scope_label'   => 'Apply to',
        'add_button'    => 'Add filter',
        'apply_button'  => 'Apply',
        'reset_button'  => 'Reset',
        'empty_hint'    => 'No filters applied. Add a filter to narrow the list.',
    ],

    'table' => [
        'header_manager'           => 'Promoter Manager',
        'header_subs'              => 'Subs',
        'header_gross_sales'       => 'Gross sales',
        'header_commission_earned' => 'Commission earned',
        'header_paid'              => 'Paid to festival',
        'header_owed'              => 'Owed to festival',
        'header_tickets'           => 'Tickets',
    ],

    'sub' => [
        'empty'                => 'No sub-promoters for this manager.',
        'header_name'          => 'Sub-promoter',
        'header_orders'        => 'Orders',
        'header_tickets'       => 'Tickets',
        'header_gross_sales'   => 'Gross sales',
        'header_commission'    => 'Commission',
        'header_paid'          => 'Paid',
        'header_owed'          => 'Owed',
        'manager_self_label'   => 'Manager direct',
    ],
];