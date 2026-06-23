<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Verifies the admin order-details Blade template still compiles after
 * the redesign and that all i18n keys referenced from the view are
 * present in both the English and Serbian translation files.
 */
class AdminOrderDetailsRenderTest extends TestCase
{
    public function test_blade_template_is_well_formed(): void
    {
        $path = resource_path('views/livewire/admin/order-details.blade.php');
        $this->assertFileExists($path);

        $compiler = app(\Illuminate\View\Compilers\BladeCompiler::class);
        $compiled = $compiler->compileString(File::get($path));
        $this->assertNotEmpty($compiled);
        $this->assertStringContainsString('<?php', $compiled);
    }

    public function test_referenced_lang_keys_exist_in_english(): void
    {
        $en = require lang_path('en/order_details.php');

        $requiredKeys = [
            'header.eyebrow',
            'header.main_heading',
            'header.sub_heading',
            'header.back_to_orders',
            'summary.customer_label',
            'summary.placed_on_label',
            'summary.total_label',
            'summary.paid_label',
            'summary.status_label',
            'tickets.card_title_prefix',
            'tickets.unknown_type',
            'tickets.status_active',
            'tickets.status_inactive',
            'tickets.qr_not_available',
            'tickets.image_not_found',
            'tickets.image_alt_prefix',
            'tickets.select_checkbox_checked',
            'tickets.select_checkbox_unchecked',
            'tickets.none_found_header',
            'tickets.none_found_message',
            'tickets.none_match_filter',
            'actions.group_title',
            'actions.download_selected_button',
            'actions.download_all_button',
            'actions.activate_selected_button',
            'actions.deactivate_selected_button',
            'actions.regenerate_button',
            'actions.regenerate_queued',
            'actions.regenerate_no_missing',
            'payment.paid_amount_label',
            'payment.update_button',
            'payment.cancel_button',
            'payment.edit_paid_button',
            'payment.paid_label',
            'filter.label',
            'filter.all_types_option',
        ];

        foreach ($requiredKeys as $key) {
            $parts = explode('.', $key);
            $node = $en;
            foreach ($parts as $p) {
                $this->assertIsArray($node, "EN order_details key '$key' is not reachable");
                $this->assertArrayHasKey($p, $node, "EN order_details key '$key' missing");
                $node = $node[$p];
            }
            $this->assertNotEmpty($node, "EN order_details key '$key' is empty");
        }
    }

    public function test_referenced_lang_keys_exist_in_serbian(): void
    {
        $sr = require lang_path('sr/order_details.php');

        $requiredKeys = [
            'header.eyebrow',
            'header.main_heading',
            'header.sub_heading',
            'header.back_to_orders',
            'summary.customer_label',
            'summary.placed_on_label',
            'summary.total_label',
            'summary.paid_label',
            'summary.status_label',
            'tickets.card_title_prefix',
            'tickets.unknown_type',
            'tickets.status_active',
            'tickets.status_inactive',
            'tickets.qr_not_available',
            'tickets.image_not_found',
            'tickets.image_alt_prefix',
            'tickets.select_checkbox_checked',
            'tickets.select_checkbox_unchecked',
            'tickets.none_found_header',
            'tickets.none_found_message',
            'tickets.none_match_filter',
            'actions.group_title',
            'actions.download_selected_button',
            'actions.download_all_button',
            'actions.activate_selected_button',
            'actions.deactivate_selected_button',
            'actions.regenerate_button',
            'actions.regenerate_queued',
            'actions.regenerate_no_missing',
            'payment.paid_amount_label',
            'payment.update_button',
            'payment.cancel_button',
            'payment.edit_paid_button',
            'payment.paid_label',
            'filter.label',
            'filter.all_types_option',
        ];

        foreach ($requiredKeys as $key) {
            $parts = explode('.', $key);
            $node = $sr;
            foreach ($parts as $p) {
                $this->assertIsArray($node, "SR order_details key '$key' is not reachable");
                $this->assertArrayHasKey($p, $node, "SR order_details key '$key' missing");
                $node = $node[$p];
            }
            $this->assertNotEmpty($node, "SR order_details key '$key' is empty");
        }
    }
}