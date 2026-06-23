<?php

namespace App\Console\Commands;

use App\Jobs\GenerateTicketImagesJob;
use App\Models\TicketOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Bulk-retry image generation for failed TicketOrders.
 *
 * Useful after fixing bugs in the image generation pipeline (for example
 * after switching the QR library from simplesoftwareio to chillerlan to
 * drop the imagick dependency). Without this command the only way to
 * retry a failed order is to click "Ponovi Slike" in the admin UI for
 * each one individually.
 *
 * Usage:
 *   php artisan orders:retry-failed                 # retry all failed
 *   php artisan orders:retry-failed --id=22         # retry specific order
 *   php artisan orders:retry-failed --dry-run       # just show what would happen
 *   php artisan orders:retry-failed --dispatch      # queue instead of running inline
 */
class RetryFailedOrdersCommand extends Command
{
    protected $signature = 'orders:retry-failed
                            {--id=* : Specific order ID(s) to retry (repeatable)}
                            {--dry-run : Show what would happen without running anything}
                            {--dispatch : Dispatch the job to the queue instead of running it synchronously}';

    protected $description = 'Retry image generation for failed ticket orders';

    public function handle(): int
    {
        $query = TicketOrder::query()->where('job_status', 'failed');

        $ids = (array) $this->option('id');
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $orders = $query->orderBy('id')->get();

        if ($orders->isEmpty()) {
            $this->info('No failed orders to retry.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d failed order(s).', $orders->count()));
        $this->table(
            ['ID', 'Email', 'Tickets', 'Last failure reason'],
            $orders->map(fn ($o) => [
                $o->id,
                $o->email,
                $o->tickets()->count(),
                \Illuminate\Support\Str::limit((string) $o->job_failure_reason, 60),
            ])
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry run - nothing was changed.');
            return self::SUCCESS;
        }

        $dispatch = (bool) $this->option('dispatch');
        $ok = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($orders as $order) {
            $this->line("Processing order #{$order->id}...");

            // Pre-flight: each ticket must have a ticket type with both a
            // readable base image and QR coordinates. Skip orders that
            // would obviously fail again so we don't loop forever.
            $blockingReasons = [];
            foreach ($order->tickets()->with('ticketType')->get() as $ticket) {
                $tt = $ticket->ticketType;
                if (!$tt) {
                    $blockingReasons[] = "ticket {$ticket->id}: no ticket type";
                    continue;
                }
                if (!$tt->photo_path || !is_file(public_path($tt->photo_path))) {
                    $blockingReasons[] = "ticket {$ticket->id}: base image missing ({$tt->photo_path})";
                }
                $coords = json_decode((string) $tt->qr_coordinates, true);
                if (!is_array($coords) || !isset($coords['x'], $coords['y'], $coords['size'])) {
                    $blockingReasons[] = "ticket {$ticket->id}: invalid QR coordinates";
                }
            }

            if (!empty($blockingReasons)) {
                $this->warn("  Skipping - configuration problem:");
                foreach ($blockingReasons as $r) {
                    $this->line("    - {$r}");
                }
                $skipped++;
                continue;
            }

            // Reset the failure state and (optionally) queue the job.
            $order->job_status = 'pending';
            $order->job_failure_reason = null;
            $order->save();

            if ($dispatch) {
                GenerateTicketImagesJob::dispatch($order->id);
                $this->info("  Queued GenerateTicketImagesJob for order #{$order->id}");
                $ok++;
            } else {
                try {
                    (new GenerateTicketImagesJob($order->id))->handle();
                    $order->refresh();
                    if ($order->job_status === 'completed') {
                        $this->info("  Order #{$order->id} -> completed");
                        $ok++;
                    } else {
                        $this->error("  Order #{$order->id} -> {$order->job_status}: " . ($order->job_failure_reason ?? ''));
                        $failed++;
                    }
                } catch (\Throwable $e) {
                    $this->error("  Order #{$order->id} threw: " . $e->getMessage());
                    $failed++;
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('Done. ok=%d skipped=%d failed=%d', $ok, $skipped, $failed));

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
