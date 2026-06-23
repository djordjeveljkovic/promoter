<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One payment event in the promoter hierarchy.
 *
 * Two payment types are supported:
 *  - 'sub_to_manager': the sub-promoter paid the promoter-manager
 *    (the cash the manager collected for orders the sub placed).
 *  - 'manager_to_organizers': the promoter-manager paid the event
 *    organizers (the net revenue after every commission in his team
 *    has been kept by the team).
 *
 * The same table can be extended with additional payment types without
 * schema changes, as long as the new type is added to the enum on the
 * migration.
 */
class SubPromoterPayment extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'payment_type',
        'payer_id',
        'receiver_id',
        'amount',
        'note',
        'recorded_by',
        'paid_at',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public const TYPE_SUB_TO_MANAGER          = 'sub_to_manager';
    public const TYPE_MANAGER_TO_ORGANIZERS   = 'manager_to_organizers';

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
