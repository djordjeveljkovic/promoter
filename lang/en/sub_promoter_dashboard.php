<?php

return [
    'page_title' => 'Sub-Promoter Dashboard',
    'eyebrow'    => 'Sub-Promoter',
    'main_heading' => 'My Sub-Promoter Dashboard',
    'managed_by_prefix' => 'Managed by:',
    'no_manager_notice' => 'You are not assigned to a promoter-manager yet. You will earn the full commission from your sales.',

    'financials' => [
        'heading'                  => 'My Financials',
        'commission_earned'        => 'My Commission Earned',
        'all_time_label'           => 'All time',
        'gross_sales'              => 'My Gross Sales',
        'gross_sales_subtext'      => 'From successful orders (completed/sent)',
        'amount_owed'              => 'What I Owe to My Manager',
        'amount_owed_subtext'      => 'Gross sales - my commission - already transferred',
        'commission_last_30'       => 'My Commission (Last 30 Days)',
        'commission_last_30_subtext' => 'Last 30 days',
        'amount_paid'              => 'Already Transferred to My Manager',
        'gross_sales_last_30'      => 'Gross Sales (Last 30 Days)',
        'debt_breakdown'           => 'How my balance is built',
        'debt_formula'             => 'Gross sales − my commission − payments already sent = balance',
        'debt_payoff_indicator'    => 'Up to date with manager',
        'debt_overpaid_indicator'  => 'You have overpaid by',
    ],

    'pyramid' => [
        'heading'           => 'How the Money Flows',
        'help'              => 'Every ticket you sell has three components: the buyer pays the full ticket price, your promoter-manager keeps their share, and you keep yours. The amount you owe to your manager is the ticket price minus only your own commission.',
        'row_gross'         => 'Gross ticket revenue from your sales',
        'row_sub_commission'=> 'Your commission (this is what YOU keep)',
        'row_amount_due'    => 'Amount you owe to your manager',
        'row_already_paid'  => 'Already paid to your manager',
        'row_remaining'     => 'Remaining balance',
    ],

    'performance' => [
        'heading'         => 'My Performance',
        'orders_all_time' => 'Orders (All Time)',
        'tickets_all_time'=> 'Tickets Sold (All Time)',
        'orders_last_30'  => 'Orders (Last 30 Days)',
        'tickets_last_30' => 'Tickets Sold (Last 30 Days)',
    ],

    'top_tickets' => [
        'heading'         => 'Top Ticket Sales by Type',
        'help'            => 'Best-selling ticket types for the orders you placed. Click an order number to view the QR codes.',
        'no_data'         => "You haven't sold any tickets yet, or no completed sales data is available.",
        'header_type'     => 'Ticket Type',
        'header_quantity' => 'Quantity Sold',
        'header_revenue'  => 'Gross Revenue',
    ],

    'status_breakdown' => [
        'heading' => 'My Order Statuses',
        'help'    => 'Breakdown of every order you placed, by job status.',
        'empty'   => 'No orders yet.',
    ],

    'commission_split' => [
        'heading'           => 'Commission Split My Manager Set',
        'help'              => 'These show how much YOU earn from each ticket type, as configured by your manager. A percentage means you receive X% of the tier-based commission your manager sets. A fixed RSD amount means you receive that flat sum per ticket, regardless of the tier.',
        'unknown_type'      => 'Unknown ticket type',
        'per_ticket_suffix' => 'per ticket',
    ],

    'recent_orders' => [
        'heading'           => 'My Recent Orders',
        'empty'             => 'No orders placed yet.',
        'empty_title'       => 'No orders yet',
        'new_order_button'  => 'New Order',
        'view_all_button'   => 'View all orders',
        'header_order'      => 'Order',
        'header_customer'   => 'Customer',
        'header_total'      => 'Total',
        'header_status'     => 'Status',
    ],

    'record_payment' => [
        'heading'           => 'Record a Payment to My Manager',
        'sub_heading'       => 'Use this when you have transferred money to your promoter-manager. Your manager will also keep a record on their side, so you can also ask them to log it.',
        'amount_label'      => 'Amount (RSD)',
        'paid_at_label'     => 'Date of payment',
        'note_label'        => 'Note (optional)',
        'submit_button'     => 'Record Payment',
        'helper_text'       => 'Recording here updates the dashboard immediately and gives you a personal journal of payments made.',
    ],

    'payment_history' => [
        'heading'           => 'My Payment History',
        'sub_heading'       => 'Every payment you have sent to your promoter-manager, newest first. Your manager\'s record is the source of truth, but you can also see your own entries here.',
        'date'              => 'Date',
        'amount'            => 'Amount',
        'direction'         => 'Direction',
        'direction_to'      => 'To manager',
        'note'              => 'Note',
        'recorded_by'       => 'Recorded by',
        'empty'             => 'No payments recorded yet.',
    ],

    'orders' => [
        'page_title'        => 'My Orders',
        'main_heading'      => 'My Orders',
        'sub_heading'       => 'Every order you placed, with the commission YOU earned on it. Orders placed by other sub-promoters are not shown here.',
        'back_to_dashboard' => '&larr; Back to dashboard',
        'new_order_button'  => 'New Order',
        'table' => [
            'header_order'         => 'Order',
            'header_customer'      => 'Customer',
            'header_date'          => 'Date',
            'header_items'         => 'Items',
            'header_total'         => 'Total',
            'header_my_commission' => 'My Commission',
            'header_status'        => 'Status',
            'header_actions'       => 'Actions',
            'actions_view_button'  => 'View / QR codes',
            'empty'                => "You haven't placed any orders yet.",
        ],
    ],
];
