<?php

return [
    'page_title'  => 'Email Settings',
    'main_heading'=> 'Email Settings',

    // Top-level tabs on the index page
    'tabs' => [
        'config'    => 'Sending Configuration',
        'templates' => 'Templates',
    ],

    // ===========================================================
    // Tab 1: Sending Configuration
    // ===========================================================
    'config' => [
        'section_sender' => [
            'heading' => 'Who sends the email',
            'help'    => 'Shown to the recipient as the sender of every email.',
        ],
        'section_server' => [
            'heading' => 'Server (SMTP)',
            'help'    => 'Connection details for your mail server. Leave a field blank to fall back to .env.',
        ],
        'section_test' => [
            'heading' => 'Test the configuration',
            'help'    => 'Sends a one-off plain-text email to verify the configuration is correct.',
        ],

        'from_name_label'         => 'Sender name',
        'from_name_placeholder'   => 'e.g. REFEST Festival',
        'from_address_label'      => 'Sender email (From address)',
        'from_address_placeholder'=> 'tickets@refest.rs',

        'mailer_label'         => 'Mail driver',
        'mailer_help'          => 'smtp for real email, log to write to laravel.log, array for tests.',
        'host_label'           => 'SMTP Host',
        'host_placeholder'     => 'mail.refest.rs',
        'port_label'           => 'SMTP Port',
        'port_placeholder'     => '465',
        'username_label'       => 'SMTP Username',
        'username_placeholder' => 'prodaja@refest.rs',
        'password_label'       => 'SMTP Password (leave blank to keep current)',
        'password_placeholder' => '•••••••• (unchanged)',
        'clear_password_label' => 'Clear stored password (fall back to .env)',
        'encryption_label'     => 'Encryption',
        'encryption_none'      => 'None',
        'timeout_label'        => 'SMTP Timeout (seconds)',
        'timeout_placeholder'  => '30',

        'test_recipient_label' => 'Default test recipient',
        'test_recipient_help'  => 'Where the test email goes when you click the button below.',
        'test_subject_label'   => 'Subject',
        'test_message_label'   => 'Message body',

        'submit_button'           => 'Save Configuration',
        'send_test_button'        => 'Send Test Email',

        'currently_effective' => 'Currently effective',
    ],

    // ===========================================================
    // Tab 2: Templates (list)
    // ===========================================================
    'templates_list' => [
        'heading'        => 'Email Templates',
        'help_text'      => 'Each template is a Blade view (recommended) or a raw HTML body. The template marked as Default is the one used when sending ticket emails.',

        'add_button'     => 'Add Template',

        'header_name'    => 'Name',
        'header_subject' => 'Subject',
        'header_source'  => 'Source',
        'header_default' => 'Default',
        'header_actions' => 'Actions',

        'source_view'    => 'Blade view',
        'source_html'    => 'Inline HTML',

        'default_badge'    => 'Default',

        'edit_button'      => 'Edit',
        'duplicate_button' => 'Duplicate',
        'delete_button'    => 'Delete',
        'make_default_button' => 'Make default',
        'delete_confirm'   => 'Delete template ":name"?',

        'empty' => 'No templates yet. Click "Add Template" to create one.',
    ],

    // ===========================================================
    // Add Template page
    // ===========================================================
    'create' => [
        'page_title'         => 'Add Email Template',
        'back_to_list'       => 'Back to Email Settings',
        'heading'            => 'Add Email Template',
        'help_text'          => 'Templates use Blade so you can drop in dynamic data with {{ $order->email }}, @foreach loops, etc. After saving you can edit the code with a live preview.',

        'name_label'        => 'Template name',
        'name_placeholder'  => 'e.g. Tickets V2',
        'subject_label'     => 'Email subject',
        'subject_placeholder' => 'e.g. Your tickets for REFEST 2025',
        'description_label' => 'Description (optional)',
        'description_placeholder' => 'Internal note describing this template',

        'source_type_label' => 'Source type',
        'source_type_view'  => 'Blade view (recommended — supports @if, @foreach, {{ $order->email }} etc.)',
        'source_type_html'  => 'Inline HTML (simple — supports {{ $orderNumber }} placeholder)',
        'view_name_label'   => 'Blade view path',
        'view_name_placeholder' => 'Leave empty to auto-create',
        'view_name_help'    => 'Pick one of the existing views (e.g. <code class="font-mono">:default</code>) to link an existing file as a template, or leave the field empty and the system will automatically create a new Blade file based on the default template. No need to create files by hand.',
        'html_content_label'=> 'HTML body',
        'html_content_placeholder' => '<h1>Hello!</h1><p>Your order {{ $orderNumber }} …</p>',

        'make_default_label' => 'Make this the default template after saving',
        'submit_button'      => 'Create Template',
        'cancel_button'      => 'Cancel',
    ],

    // ===========================================================
    // Edit Template page (split view)
    // ===========================================================
    'edit' => [
        'page_title'       => 'Edit Email Template',
        'back_to_list'     => 'Back to Email Settings',

        'name_label'       => 'Name',
        'subject_label'    => 'Email subject',
        'description_label'=> 'Description',

        'make_default_label' => 'Use as default template',
        'make_default_help'  => 'When checked, this template is used for sending ticket emails. Only one template can be the default at a time.',

        'editor_heading'   => 'Source code',
        'preview_heading'  => 'Live preview',
        'preview_help'     => 'Rendered with sample data — the order number, customer name, ticket types and images shown here are placeholders so you can see how the email looks before sending it for real.',
        'preview_refresh_button' => 'Refresh preview',
        'preview_iframe_title'   => 'Email preview',

        'save_metadata_button'   => 'Save settings',
        'save_source_button'     => 'Save code',

        'source_size'         => ':size KB',
        'source_missing'      => 'Underlying file is missing: :path',

        'editor_blade_variables' => 'Available Blade variables: <code>$order</code> (TicketOrder), <code>$currencySymbol</code>, <code>$template</code>.',

        'danger_heading'   => 'Danger zone',
        'danger_help'      => 'Deleting a template also removes its generated Blade file. This cannot be undone.',
        'delete_button'    => 'Delete template',
        'delete_confirm'   => 'Delete template ":name"?',
    ],

    // ===========================================================
    // Test email
    // ===========================================================
    'test_email' => [
        'default_subject' => 'Test email from :app_name',
        'default_body'    => "Hello!\n\nThis is a test email sent from :app_name to verify the mail configuration.\n\nMailer: :mailer\nHost: :host\nPort: :port\n\nIf you received this, the configuration is working.",
    ],

    'duplicate_suffix' => '(duplicated)',
];
