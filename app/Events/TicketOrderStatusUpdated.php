<?php

namespace App\Events;

use App\Models\TicketOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast whenever a TicketOrder's job_status changes.
 *
 * Listened to by admin/supreme clients (e.g. the orders index page) so the
 * status badge and action buttons update without a manual page refresh.
 *
 * Uses ShouldBroadcastNow (synchronous broadcast) so listeners see the new
 * state immediately when the job finishes, rather than waiting for the
 * broadcasting queue to be drained. This is safe because Reverb is a local
 * HTTP endpoint in this stack — the broadcast is a single short request.
 *
 * If you ever move Reverb off-box or behind a slow link, switch to
 * ShouldBroadcast to keep job latency independent of broadcast latency.
 */
class TicketOrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  int  $orderId  Primary key of the affected TicketOrder.
     * @param  string  $status  New job_status value (pending|processing|completed|sent|failed|...).
     * @param  string|null  $failureReason  job_failure_reason (null when not failed).
     * @param  string|null  $previousStatus  Previous status, for clients that only react to certain transitions.
     */
    public function __construct(
        public int $orderId,
        public string $status,
        public ?string $failureReason = null,
        public ?string $previousStatus = null,
    ) {
    }

    /**
     * Convenience factory so callers don't have to repeat the field names.
     */
    public static function fromOrder(TicketOrder $order, ?string $previousStatus = null): self
    {
        return new self(
            orderId: (int) $order->id,
            status: (string) ($order->job_status ?? 'unknown'),
            failureReason: $order->job_failure_reason,
            previousStatus: $previousStatus,
        );
    }

    /**
     * The orders channel is private so only admin / supreme users can
     * subscribe — channel authorization is in routes/channels.php.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('orders')];
    }

    /**
     * Use a dot-namespaced event name on the wire. Clients listen via
     * `Echo.private('orders').listen('.order.status.updated', ...)`.
     */
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    /**
     * Only forward the data clients actually need; do NOT leak the full
     * TicketOrder (it contains customer email, payment amounts, etc.).
     */
    public function broadcastWith(): array
    {
        return [
            'order_id'        => $this->orderId,
            'status'          => $this->status,
            'failure_reason'  => $this->failureReason,
            'previous_status' => $this->previousStatus,
        ];
    }
}