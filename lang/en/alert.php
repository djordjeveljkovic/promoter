<?php // resources/lang/en/alert.php

return [
    // Ticket Type Alerts
    'ticket_type_created_success' => 'Ticket Type created successfully!',
    'ticket_type_create_failed'   => 'Failed to create Ticket Type. Please try again. Error: :error',

    'ticket_type_updated_success' => 'Ticket Type updated successfully!',
    'ticket_type_update_failed'   => 'Failed to update Ticket Type. Please try again. Error: :error',

    'ticket_type_deleted_success' => 'Ticket Type deleted successfully!',
    'ticket_type_delete_failed'   => 'Failed to delete Ticket Type. Error: :error',

    'failed_to_create_directory' => 'Failed to create directory: :error',
    'failed_to_move_photo'       => 'Failed to move uploaded photo: :error',
    'update_failed_create_directory' => 'Update failed: Could not create directory: :error',
    'update_failed_move_photo'       => 'Update failed: Could not move new photo: :error',

    'order_created_success' => 'Order created successfully! Processing initiated for order .',
    'order_created_failure' => 'Failed to create order due to an internal error: :message',
    'image_generation_requeued' => 'Image generation for order has been re-queued.',
    'image_generation_cannot_rerun' => 'Image generation for order cannot be rerun from its current state (:status).',
    'email_requeued_success' => 'Email for order has been re-queued for sending.',
    'email_cannot_resent' => 'Email for order cannot be re-sent from its current state (:status).',

    'payment_amount_updated' => 'Payment amount updated.',
    'ticket_codes_not_found' => 'None of the selected ticket codes were found for this order.',
    'no_tickets_to_process' => 'No tickets available to process for this order.',
    'no_qr_codes_found' => 'No QR code images were found for the specified tickets.',
    'zip_creation_failed' => 'Could not create the ZIP file. Please check server permissions or logs.',

    'promoter_updated_success' => 'Promoter updated successfully!',
    'auth_required' => 'Authentication required.',

    // Promoter-manager alerts
    'promoter_manager_created_success' => 'Promoter Manager created successfully!',
    'promoter_manager_updated_success' => 'Promoter Manager updated successfully!',
    'promoter_manager_deleted_success' => 'Promoter Manager deleted successfully!',
    'sub_promoter_created_success'     => 'Sub-Promoter created successfully!',
    'sub_promoter_updated_success'     => 'Sub-Promoter updated successfully!',
    'sub_promoter_deleted_success'     => 'Sub-Promoter deleted successfully!',
    'ticket_type_created_success' => 'Ticket Type created successfully!',
    'ticket_type_create_failed' => 'Failed to create Ticket Type. Please try again. Error: :message',
    'ticket_type_updated_success' => 'Ticket Type updated successfully!',
    'ticket_type_update_failed' => 'Failed to update Ticket Type. Error: :message',
    'ticket_type_deleted_success' => 'Ticket Type deleted successfully!',
    'ticket_type_delete_failed' => 'Failed to delete Ticket Type. Error: :message',

    'password_update_success' => 'Password updated successfully!',
    'password_update_failed' => 'Failed to update password. Please try again.',
    'validation_failed_check_fields' => 'Validation failed. Please check the input fields for errors.',

    // Email template alerts
    'email_template_created'           => 'Email template ":name" created successfully.',
    'email_template_updated'           => 'Email template ":name" updated successfully.',
    'email_template_activated'         => 'Email template ":name" is now active. All outgoing ticket emails will use this template.',
    'email_template_deleted'           => 'Email template ":name" deleted.',
    'email_template_name_required'     => 'Template name is required.',
    'email_template_name_unique'       => 'A template with this name already exists.',
    'email_template_subject_required'  => 'Email subject is required.',
    'email_template_view_string'       => 'Blade view path must be a string.',
    'email_template_view_not_found'    => 'Blade view ":view" does not exist. Please create the view file first or leave the field empty.',
    'email_template_needs_view_or_html' => 'You must provide either a Blade view path or an inline HTML body for the template.',
    'email_template_source_saved'      => 'Source code for template ":name" saved.',
    'email_template_duplicated'        => 'Template ":name" created as a copy. You can now edit it independently.',
    'email_template_imported'          => 'Default email imported as template ":name". You can now edit it.',

    // Mail config (admin → /admin/email-settings)
    'email_settings_saved'                  => 'Email configuration saved. New values are now active.',
    'email_settings_mailer_invalid'         => 'Mailer must be one of: smtp, sendmail, log, array, failover, roundrobin.',
    'email_settings_port_invalid'           => 'Port must be a number between 1 and 65535.',
    'email_settings_encryption_invalid'     => 'Encryption must be "tls", "ssl", or left blank.',
    'email_settings_from_address_invalid'   => 'From address must be a valid email.',
    'email_settings_test_recipient_invalid' => 'Recipient must be a valid email address.',
    'email_settings_test_recipient_required'=> 'No recipient configured. Set a "Test Recipient" under mail settings or pass a "to" address.',
    'email_settings_test_sent'              => 'Test email sent to :to.',
    'email_settings_test_failed'            => 'Test email failed: :error',
];
