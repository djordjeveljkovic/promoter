<?php // resources/lang/en/promoter_orders.php

return [
    'page_title' => 'My Ticket Orders',
    'main_heading' => 'My Placed Ticket Orders',
    'create_new_order_button' => 'Create New Order',

    'table' => [
        'header_order_id' => 'Order ID',
        'header_seller'   => 'Seller',
        'header_customer_email' => 'Customer Email',
        'header_order_date' => 'Order Date',
        'header_items' => 'Items',
        'header_total_price' => 'Total Price',
        'header_commission_earned' => 'Commission Earned',
        'header_job_status' => 'Job Status',
        'header_actions' => 'Actions',

        'commission_not_calculated' => 'Not Calculated',
        'status_error_tooltip_prefix' => 'Click to view error details:',
        'actions_retry_images_button' => 'Retry Images',
        'actions_retry_images_tooltip_prefix' => 'Retry generating images/QR codes. Failure:',
        'actions_retry_email_button' => 'Retry Email',
        'actions_retry_email_tooltip_prefix' => 'Retry sending email. Failure:',
        'actions_resend_email_button' => 'Resend Email',
        'actions_resend_email_tooltip' => 'Resend confirmation email.',
        'actions_view_button' => 'View',
        'actions_view_tooltip' => 'View order details and tickets',
        'job_failure_reason_label' => 'Job Failure Reason:',
        'no_orders_message' => "You haven't placed any orders yet.",
    ],

    'create_page_title' => 'Create New Ticket Order',
    'create_main_heading' => 'Create New Ticket Order',
    'create_back_to_orders_link' => '&larr; Back to Orders',
    'create_customer_email_label' => 'Customer Email', // The asterisk is part of the HTML structure

    'create_order_items_heading' => 'Order Items',
    'create_ticket_type_label' => 'Ticket Type',
    'create_select_ticket_type_option' => 'Select a ticket type...',
    'create_quantity_label' => 'Quantity',
    'create_add_item_button' => 'Add Item',

    'create_items_table_header_ticket' => 'Ticket',
    'create_items_table_header_quantity' => 'Quantity',
    'create_items_table_header_unit_price' => 'Unit Price',
    'create_items_table_header_subtotal' => 'Subtotal',
    'create_items_table_header_remove' => 'Remove',
    'create_no_items_message' => 'No items added yet.',

    'create_total_label' => 'Total',
    'create_cancel_button' => 'Cancel',
    'create_submit_button' => 'Place Order & Send Tickets',

    // Commission split banner shown to sub-promoters on the create-order form
    'commission_split_notice_title'       => 'Commission Split',
    'commission_split_notice_managed_by'  => 'Your promoter-manager is :name. Your commission will be split per the rules below:',
    'commission_split_notice_default'     => 'No specific commission split is configured. You will keep 100% of the commission on your sales.',
    'commission_split_notice_no_manager'  => 'You are not assigned to a promoter-manager, so you will keep 100% of the commission.',
    // Translatable job statuses
    'statuses' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'failed' => 'Failed',
        'blocked' => 'Blocked',
        'completed' => 'Completed',
        'sent' => 'Sent',
        'unknown' => 'N/A', // Fallback for undefined status
    ],

    'seller_self_badge' => 'You',
    'seller_unknown'    => 'Unknown seller',

    'show_page_title' => 'Order :orderNumber',
    'show' => [
        'eyebrow'                       => 'Order details',
        'main_heading'                  => 'Order #:orderNumber',
        'sub_heading'                   => 'Full order details with the commission you personally earned and the QR codes for every ticket sold.',
        'back_to_orders'                => '&larr; Back to orders',
        'summary' => [
            'customer_label'          => 'Customer',
            'placed_on_label'         => 'Placed on',
            'total_label'             => 'Total',
            'status_label'            => 'Status',
            'seller_label'            => 'Sold by',
            'my_commission_label'     => 'My commission on this order',
            'commission_split_note'   => 'Total commission pool was :total RSD - the rest went to the seller\'s sub-promoter per their override.',
        ],
        'items' => [
            'heading'           => 'Items',
            'header_type'       => 'Ticket type',
            'header_quantity'   => 'Qty',
            'header_unit_price' => 'Unit price',
            'header_subtotal'   => 'Subtotal',
            'unknown_type'      => 'Unknown type',
        ],
        'tickets' => [
            'heading'              => 'Tickets & QR codes',
            'sub_heading'          => ':count ticket(s) generated for this order. Click any QR image to view full size.',
            'empty'                => 'No tickets have been generated for this order yet.',
            'image_alt_prefix'     => 'QR code for ticket',
            'card_title_prefix'    => 'Ticket #',
            'unknown_type'         => 'Unknown type',
            'status_active'        => 'Active',
            'status_inactive'      => 'Inactive',
            'qr_not_available'     => 'QR not generated yet',
            'download_all_button'  => 'Download all QR codes',
        ],
    ],
];
