<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Events\TicketOrderStatusUpdated;
use chillerlan\QRCode\QRCode as ChillerlanQrCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Render a QR-branded image for every ticket of a TicketOrder.
 *
 * Performance notes
 * -----------------
 * The previous version of this job processed one order's tickets as a single
 * monolithic loop. For large orders (100+ tickets of the same TicketType)
 * this could easily exceed the queue worker's per-job timeout and look
 * "stuck on running". The optimizations below target the three things that
 * dominated that runtime:
 *
 *   1. The base photo was re-decoded from disk once per ticket. We now
 *      decode each unique TicketType's photo exactly once and reuse the
 *      resulting GD resource across all tickets of that type.
 *   2. Final PNGs were written at maximum compression (level 9). For these
 *      ticket images level 6 is visually indistinguishable and 3-10x
 *      faster to encode.
 *   3. Final PNG bytes were captured with ob_start/ob_get_clean and then
 *      handed to Storage::put. We now write the PNG directly to disk via
 *      imagepng($gd, $absPath, 6), skipping the in-memory round-trip.
 *
 * Resilience notes
 * ----------------
 * Transient failures (DB blip, FS hiccup) now retry up to $tries times
 * with $backoff seconds between attempts. A timeout still fails the job
 * permanently (failOnTimeout = true) so we never re-run partial work that
 * could leave inconsistent state behind.
 */
class GenerateTicketImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Per-job wall-clock limit (seconds). Keep comfortably below the queue
     * worker's --timeout so the job fails cleanly instead of being killed.
     */
    public int $timeout = 300;

    /**
     * Retry transient failures (DB blip, FS hiccup) up to this many times.
     * Note: when run via `php artisan queue:listen --tries=N`, that flag
     * overrides this value. `queue:work` respects the job-level setting.
     */
    public int $tries = 3;

    /**
     * Seconds to wait between retry attempts.
     */
    public int $backoff = 30;

    /**
     * Do NOT retry after a timeout: the worker was killed mid-work and the
     * DB may have partial updates. Re-running is wasteful at best and could
     * produce duplicate side-effects at worst.
     */
    public bool $failOnTimeout = true;

    /**
     * PNG compression level for the final composited image.
     * 0 = fastest/largest, 9 = slowest/smallest. 6 is the sweet spot for
     * ticket images: indistinguishable from level 9 to the human eye, but
     * typically 3-10x faster to encode.
     */
    private const PNG_COMPRESSION = 6;

    public int $ticketOrderId;

    public function __construct(int $ticketOrderId)
    {
        $this->ticketOrderId = $ticketOrderId;
    }

    public function handle(): void
    {
        Log::info("[GenerateTicketImagesJob] Starting for Order ID: {$this->ticketOrderId}");

        $order = TicketOrder::with('tickets.ticketType')->find($this->ticketOrderId);
        if (!$order) {
            Log::warning("[GenerateTicketImagesJob] Order {$this->ticketOrderId} not found, skipping job.");
            return;
        }

        // Per-ticket-type cached GD resources. Many tickets in a single
        // order share the same TicketType, so reusing the decoded base
        // image saves N-1 JPEG/PNG decodes per ticket type.
        $baseImages = [];
        $processedCount = 0;

        try {
            foreach ($order->tickets as $ticket) {
                $this->generateSingleTicket($ticket, $order, $baseImages);
                $processedCount++;
            }

            $previousStatus = (string) $order->job_status;
            $order->update([
                'job_status' => 'completed',
                'job_failure_reason' => null,
            ]);
            $this->broadcastStatus($order, $previousStatus);
            Log::info("[GenerateTicketImagesJob] Finished generating {$processedCount} images for Order ID: {$this->ticketOrderId}");
        } catch (Throwable $e) {
            $this->markOrderFailed($order, $e);
            // markOrderFailed() updated the DB; broadcast the new 'failed' status
            // so admin dashboards react in real time instead of needing a refresh.
            $this->broadcastStatus($order->fresh() ?? $order, (string) ($order->job_status ?? null));
            Log::error("[GenerateTicketImagesJob] Failed for Order ID: {$this->ticketOrderId}. Processed {$processedCount}/" . count($order->tickets), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Re-throw so the queue worker counts this attempt as a failure
            // and (if tries remain) schedules a retry. After all retries are
            // exhausted, Laravel invokes failed() below.
            throw $e;
        } finally {
            // Always release GD memory for cached base images, even on
            // exception or timeout. Without this, a 200-ticket order of
            // 2 MB base photos would hold ~400 MB of GD memory until the
            // worker shuts down.
            foreach ($baseImages as $resource) {
                if ($resource instanceof \GdImage) {
                    imagedestroy($resource);
                }
            }
        }
    }

    /**
     * Render a single ticket image and persist it.
     *
     * @param  array<int, \GdImage>  $baseImagesCache  Per-ticket-type GD resources; populated lazily.
     */
    private function generateSingleTicket(Ticket $ticket, TicketOrder $order, array &$baseImagesCache): void
    {
        $ticketType = $ticket->ticketType;
        if (!$ticketType || !$ticketType->photo_path || !$ticketType->qr_coordinates) {
            throw new \RuntimeException("Missing ticket type, base photo, or QR coordinates for Ticket ID {$ticket->id}.");
        }

        $baseImagePath = public_path($ticketType->photo_path);
        if (!is_file($baseImagePath)) {
            throw new \RuntimeException("Base image not found at {$baseImagePath} for Ticket ID {$ticket->id}.");
        }

        $qrCoords = json_decode((string) $ticketType->qr_coordinates, true);
        if (!is_array($qrCoords) || !isset($qrCoords['x'], $qrCoords['y'], $qrCoords['size'])) {
            throw new \RuntimeException("Invalid QR coordinates format for Ticket ID {$ticket->id}.");
        }
        $qrX = (int) $qrCoords['x'];
        $qrY = (int) $qrCoords['y'];
        $qrSize = max(50, (int) $qrCoords['size']);

        // Get-or-load the cached base image for this ticket type.
        if (!isset($baseImagesCache[$ticketType->id])) {
            $baseImagesCache[$ticketType->id] = $this->loadBaseImageGd($baseImagePath);
        }
        /** @var \GdImage $cachedBase */
        $cachedBase = $baseImagesCache[$ticketType->id];

        // imagecopy() mutates the destination, so we must NOT draw on the
        // cached base. Clone it for this ticket.
        $workingImage = $this->cloneGdImage($cachedBase);
        if ($workingImage === false) {
            throw new \RuntimeException("Failed to clone base image for Ticket ID {$ticket->id}.");
        }

        $qrGd = null;
        try {
            // --- Generate QR code PNG bytes ---
            // --- Generate QR code PNG bytes ---
            // Uses chillerlan/php-qrcode with the GD PNG output. Unlike
            // simplesoftwareio/simple-qrcode which requires the ext-imagick
            // PHP extension for PNG output, chillerlan's QRGdImagePNG works
            // on top of standard ext-gd which is enabled in most PHP builds.
            $qrPng = $this->generateQrPng($ticket->code, $qrSize);
            $qrGd = @imagecreatefromstring($qrPng);
            if (!$qrGd) {
                throw new \RuntimeException("Failed to decode QR PNG for Ticket ID {$ticket->id}.");
            }

            // --- Composite QR onto base image ---
            imagealphablending($workingImage, true);
            imagesavealpha($workingImage, true);
            if (!imagecopy($workingImage, $qrGd, $qrX, $qrY, 0, 0, imagesx($qrGd), imagesy($qrGd))) {
                throw new \RuntimeException("Failed to copy QR onto base image for Ticket ID {$ticket->id}.");
            }

            // --- Save final PNG directly to disk ---
            // Skips the ob_start/ob_get_clean + Storage::put round-trip
            // used by the previous implementation.
            $finalRel = "generated_tickets/{$order->id}/{$ticket->code}.png";
            $finalAbs = $this->ensureStoragePath($finalRel);
            if (!imagepng($workingImage, $finalAbs, self::PNG_COMPRESSION)) {
                throw new \RuntimeException("Failed to encode final PNG to {$finalRel} for Ticket ID {$ticket->id}.");
            }

            // --- Save raw QR PNG ---
            $qrRel = "qr_images/{$order->id}/{$ticket->code}.png";
            $qrAbs = $this->ensureStoragePath($qrRel);
            if (file_put_contents($qrAbs, $qrPng) === false) {
                throw new \RuntimeException("Failed to save raw QR PNG to {$qrRel} for Ticket ID {$ticket->id}.");
            }

            // --- Single DB update for both paths (was two updates before) ---
            $ticket->update([
                'image_path'   => $finalRel,
                'qr_code_path' => $qrRel,
            ]);

            Log::debug("[GenerateTicketImagesJob] Generated image for Ticket ID {$ticket->id} at {$finalRel}");
        } finally {
            if ($qrGd instanceof \GdImage) {
                imagedestroy($qrGd);
            }
            if ($workingImage instanceof \GdImage) {
                imagedestroy($workingImage);
            }
        }
    }

    /**
     * Generate a PNG-encoded QR code for the given payload using
     * chillerlan/php-qrcode's GD output.
     *
     * We use chillerlan instead of simplesoftwareio/simple-qrcode because
     * the latter hardcodes `ImagickImageBackEnd` for PNG output, which
     * requires the ext-imagick PHP extension that is not always available
     * on production servers. chillerlan's `QRGdImagePNG` works on top of
     * the standard ext-gd extension that is enabled in the vast majority of
     * PHP builds.
     *
     * The QR matrix is rendered at 10 px per module and then downscaled to
     * `$size` px, which produces crisper round-corner modules than a direct
     * low-resolution render.
     *
     * @return string Raw PNG bytes (suitable for `imagecreatefromstring`).
     */
    private function generateQrPng(string $payload, int $size): string
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException('ext-gd is required for QR code generation but is not loaded.');
        }

        $options = new QROptions();
        $options->outputType   = QROutputInterface::GDIMAGE_PNG;
        $options->outputBase64 = false;
        $options->returnResource = false;
        // chillerlan renders at $scale px per module; oversize then downscale
        // for crisp circles. scale=10 is its recommended starting point.
        $options->scale  = 10;
        // Quiet zone is set in modules by the library; we keep the default
        // margin so scanners have plenty of whitespace around the code.
        $options->quietzoneSize = 4;
        $options->eccLevel = \chillerlan\QRCode\Common\EccLevel::H;

        $renderer = new ChillerlanQrCode($options);
        $png = $renderer->render($payload);
        if (!is_string($png) || $png === '') {
            throw new \RuntimeException('chillerlan QR renderer returned an empty PNG payload.');
        }

        // Downscale to the requested size if the renderer overshot (it always
        // does at scale=10 - it renders an N*scale square; we want exactly
        // $size px). We use imagecreatefromstring + imagescale to keep the
        // pipeline purely GD-based.
        if (function_exists('imagecreatefromstring') && function_exists('imagescale')) {
            $src = @imagecreatefromstring($png);
            if ($src instanceof \GdImage) {
                $srcW = imagesx($src);
                if ($srcW !== $size) {
                    $dst = imagescale($src, $size, $size);
                    if ($dst instanceof \GdImage) {
                        ob_start();
                        imagepng($dst, null, 6);
                        $scaled = ob_get_clean();
                        imagedestroy($dst);
                        imagedestroy($src);
                        if (is_string($scaled) && $scaled !== '') {
                            return $scaled;
                        }
                    }
                }
                imagedestroy($src);
            }
        }

        return $png;
    }

    /**
     * Decode a base image into a GD resource.
     *
     * @throws \RuntimeException on missing file, unreadable header, or unsupported type.
     */
    private function loadBaseImageGd(string $path): \GdImage
    {
        $info = @getimagesize($path);
        if ($info === false) {
            throw new \RuntimeException("Failed to read base image info: {$path}");
        }

        $gd = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/gif'  => @imagecreatefromgif($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => throw new \RuntimeException("Unsupported base image type: {$info['mime']}"),
        };

        if (!$gd) {
            throw new \RuntimeException("Failed to decode base image: {$path}");
        }

        return $gd;
    }

    /**
     * Duplicate a GD image while preserving alpha channel.
     *
     * GD has no public clone operator; create a new truecolor canvas of the
     * same dimensions, fill it with a fully transparent color, and copy the
     * source over. imagecopy() mutates the destination, so we MUST do this
     * instead of drawing on the cached base image directly.
     */
    private function cloneGdImage(\GdImage $source): \GdImage|false
    {
        $w = imagesx($source);
        $h = imagesy($source);
        $clone = imagecreatetruecolor($w, $h);
        if ($clone === false) {
            return false;
        }
        imagealphablending($clone, false);
        imagesavealpha($clone, true);
        $transparent = imagecolorallocatealpha($clone, 0, 0, 0, 127);
        imagefilledrectangle($clone, 0, 0, $w, $h, $transparent);
        imagealphablending($clone, true);
        if (!imagecopy($clone, $source, 0, 0, 0, 0, $w, $h)) {
            imagedestroy($clone);
            return false;
        }
        return $clone;
    }

    /**
     * Resolve a relative Storage path to an absolute filesystem path,
     * creating parent directories on demand.
     */
    private function ensureStoragePath(string $relative): string
    {
        $abs = Storage::disk('public')->path($relative);
        $dir = dirname($abs);
        if (!is_dir($dir)) {
            // @mkdir is idempotent; this is the one place we still suppress
            // the warning because "already exists" races are harmless and
            // expected on a busy queue.
            @mkdir($dir, 0775, true);
        }
        return $abs;
    }

    /**
     * Fire a TicketOrderStatusUpdated event so admin/supreme dashboards
     * refresh in real time. Wrapped in try/catch so a broadcast failure
     * (e.g. Reverb unreachable) never affects the job's outcome — the
     * queue worker must not retry the whole image-generation job just
     * because a websocket endpoint is down.
     */
    private function broadcastStatus(TicketOrder $order, ?string $previousStatus): void
    {
        try {
            TicketOrderStatusUpdated::dispatch(
                (int) $order->id,
                (string) ($order->job_status ?? 'unknown'),
                $order->job_failure_reason,
                $previousStatus,
            );
        } catch (Throwable $broadcastError) {
            Log::warning("[GenerateTicketImagesJob] Failed to broadcast status for Order {$this->ticketOrderId}: " . $broadcastError->getMessage());
        }
    }

    /**
     * Persist a failure status on the order. Best-effort: if the order
     * itself is gone or the DB write fails, we still want the queue to
     * record the job failure.
     */
    private function markOrderFailed(?TicketOrder $order, Throwable $e): void
    {
        if (!$order) {
            Log::warning("[GenerateTicketImagesJob] Order {$this->ticketOrderId} could not be loaded to mark as failed.");
            return;
        }
        try {
            $order->update([
                'job_status' => 'failed',
                'job_failure_reason' => $e->getMessage(),
            ]);
        } catch (Throwable $updateError) {
            Log::error("[GenerateTicketImagesJob] Could not update order {$this->ticketOrderId} after failure", [
                'update_error' => $updateError->getMessage(),
            ]);
        }
    }

    /**
     * Laravel invokes this after all retry attempts are exhausted, or when
     * the job is force-failed. Acts as a safety net for cases where the
     * worker died (e.g. OOM kill) before the catch block could update the
     * order.
     */
    public function failed(Throwable $e): void
    {
        Log::error("[GenerateTicketImagesJob] Job permanently failed for Order ID: {$this->ticketOrderId}", [
            'error' => $e->getMessage(),
        ]);
        $order = TicketOrder::find($this->ticketOrderId);
        if ($order && $order->job_status !== 'completed') {
            try {
                $order->update([
                    'job_status' => 'failed',
                    'job_failure_reason' => $e->getMessage(),
                ]);
            } catch (Throwable $updateError) {
                Log::error("[GenerateTicketImagesJob] failed() could not update order {$this->ticketOrderId}", [
                    'update_error' => $updateError->getMessage(),
                ]);
            }
        }
    }
}