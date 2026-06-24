<?php

return [
    'page_title'         => 'Supreme Admin Overview',
    'main_heading'       => 'Supreme Admin Overview',
    'sub_heading'        => 'Bird\'s-eye view of every promoter-manager, their promoters, and how much each one has earned or owes the festival.',
    'search_placeholder' => 'Search by name or email…',
    'search_button'      => 'Search',
    'clear_button'       => 'Clear',
    'empty_results'      => 'No promoter-managers match the current filters.',

    'kpi' => [
        'managers'    => 'Managers',
        'subs'        => 'Promoters',
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
        'empty'                => 'No promoters for this manager.',
        'header_name'          => 'Promoter',
        'header_orders'        => 'Orders',
        'header_tickets'       => 'Tickets',
        'header_gross_sales'   => 'Gross sales',
        'header_commission'    => 'Commission',
        'header_paid'          => 'Paid',
        'header_owed'          => 'Owed',
        'manager_self_label'   => 'Manager direct',
    ],

    'users' => [
        'page_title'           => 'User Management',
        'main_heading'         => 'All users',
        'sub_heading'          => 'Browse, search and delete any account in the system. Only supreme admins can reach this page.',
        'search_placeholder'   => 'Search by name or email…',
        'search_button'        => 'Search',
        'clear_button'         => 'Clear',
        'all_roles_option'     => 'All roles',
        'empty_results'        => 'No users match the current filters.',
        'empty_results_hint'   => 'Try clearing the search box or pick a different role.',

        'role_supreme'         => 'Supreme',
        'role_superadmin'      => 'Superadmin',
        'role_admin'           => 'Admin',
        'role_promoter'        => 'Promoter',
        'role_promoter_manager'=> 'Promoter Manager',
        'role_sub_promoter'    => 'Promoter',

        'table' => [
            'header_name'         => 'Name',
            'header_role'         => 'Role',
            'header_parent'       => 'Manager',
            'header_orders'       => 'Orders',
            'header_subs'         => 'Promoters',
            'header_joined_date'  => 'Joined',
            'header_actions'      => 'Actions',
        ],

        'action_delete'        => 'Delete',
        'action_locked'        => 'Locked',
        'delete_confirm_message' => 'Permanently delete :name? This cannot be undone.',
    ],
];