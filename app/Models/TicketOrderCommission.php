<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-beneficiary commission record for a ticket order item.
 *
 * When an order is finalised, one row is created per beneficiary that earns
 * a share of the item commission. For a regular promoter / promoter_manager
 * order this is a single row. For a sub-promoter order it is typically two
 * rows (sub-promoter share + promoter-manager share).
 */
class TicketOrderCommission extends Model
{
    protected $fillable = [
        'ticket_order_id',
        'ticket_order_item_id',
        'beneficiary_user_id',
        'beneficiary_role',
        'quantity',
        'commission_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'commission_amount' => 'decimal:2',
    ];

    public function ticketOrder(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class);
    }

    public function ticketOrderItem(): BelongsTo
    {
        return $this->belongsTo(TicketOrderItem::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }
}
