<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-(manager, sub-promoter, ticket-type) commission delegation rule.
 *
 * Two commission modes are supported and selected by `commission_type`:
 *
 *  - 'percentage' (default): the sub-promoter earns `commission_percentage`
 *    % of the manager's tier-based commission for every ticket of that type
 *    sold. The remaining (100 - percentage) is kept by the manager.
 *
 *  - 'fixed': the sub-promoter earns `fixed_commission_amount` RSD per
 *    ticket (independent of the manager's tier). The promoter-manager
 *    keeps the difference between the tier-based gross commission and the
 *    fixed share.
 */
class PromoterCommissionOverride extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'promoter_manager_id',
        'sub_promoter_id',
        'ticket_type_id',
        'commission_percentage',
        'commission_type',
        'fixed_commission_amount',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'commission_percentage'   => 'decimal:2',
        'fixed_commission_amount' => 'decimal:2',
    ];

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED      = 'fixed';

    public function promoterManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoter_manager_id');
    }

    public function subPromoter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sub_promoter_id');
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    /**
     * Convenience accessor: is this override configured as a fixed-amount
     * delegation (i.e. the promoter-manager chose to pay a flat RSD amount
     * per ticket instead of a percentage of the tier commission)?
     */
    public function isFixed(): bool
    {
        return $this->commission_type === self::TYPE_FIXED
            && $this->fixed_commission_amount !== null
            && (float) $this->fixed_commission_amount > 0;
    }
}
