<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'paid',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function subPromoters(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function ticketOrders(): HasMany
    {
        return $this->hasMany(TicketOrder::class, 'ordered_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(TicketCommission::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn(string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    // In your User or Promoter model (or a dedicated CommissionService)
    public static function calculateCommission(
        $ticketTypeId,
        $ticketOrderId, // Used to determine sales before this order
        $quantity,      // Quantity of the current item being calculated
        $user,          // The promoter User model instance
        \DateTimeInterface $orderCreatedAtDate // Pass the order's creation_at timestamp
    ) {
        // 1. Calculate total quantity of this ticket_type_id sold by this user in *completed* orders
        //    that were created *before* the current ticketOrderId. This establishes the baseline.
        //    Using ticketOrderId assumes IDs are sequential and reflect creation order.
        //    If not, you might need a more complex query based on created_at < $orderCreatedAtDate.
        $promoterId = is_object($user) ? $user->id : $user; // Get ID if $user is object

        $quantityPreviousOrders = TicketOrder::join('ticket_order_items', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->where('ticket_orders.job_status', 'completed') // Or your array of successfulSaleStatuses
            ->where('ticket_orders.id', '<', $ticketOrderId)
            ->where('ticket_order_items.ticket_type_id', $ticketTypeId)
            ->where('ticket_orders.requested_by', $promoterId)
            ->sum('ticket_order_items.quantity');

        // 2. Get all commission tiers for this ticket type, active at the time the order was created
        $commissionTiers = TicketCommission::where('ticket_type_id', $ticketTypeId)
            ->where('valid_from', '<=', $orderCreatedAtDate)
            ->where(function ($query) use ($orderCreatedAtDate) {
                $query->where('valid_to', '>=', $orderCreatedAtDate)
                    ->orWhereNull('valid_to');
            })
            ->orderBy('min_sold', 'asc')
            ->get();

        if ($commissionTiers->isEmpty()) {
            Log::warning("No active commission tiers found for ticket_type_id: {$ticketTypeId} at order creation date: " . $orderCreatedAtDate->format('Y-m-d H:i:s'));
            return 0.0;
        }

        // 3. Calculate commission based on how the current item's quantity falls into these historical tiers
        $commission = 0.0;
        // $quantityPreviousOrders is the count *before* this order's items.
        // We are calculating for a block of '$quantity' new items for the current order.

        foreach ($commissionTiers as $tier) {
            $minSoldTier = $tier->min_sold;
            // If max_sold is 0 or null, consider it effectively infinite for this tier's upper bound.
            $maxSoldTier = ($tier->max_sold === null || $tier->max_sold == 0) ? PHP_INT_MAX : $tier->max_sold;
            $commissionAmountPerUnitInTier = $tier->commission_amount;

            // Determine the range of sales numbers covered by the current order's items
            $startSaleNumberOfCurrentOrder = $quantityPreviousOrders + 1;
            $endSaleNumberOfCurrentOrder = $quantityPreviousOrders + $quantity;

            // Find the overlap between the current order's sale numbers and the tier's range
            $overlapStart = max($startSaleNumberOfCurrentOrder, $minSoldTier);
            $overlapEnd = min($endSaleNumberOfCurrentOrder, $maxSoldTier);

            if ($overlapStart <= $overlapEnd) {
                $unitsInThisTierFromCurrentOrder = $overlapEnd - $overlapStart + 1;
                $commission += $unitsInThisTierFromCurrentOrder * $commissionAmountPerUnitInTier;
                Log::info("Order {$ticketOrderId}, Item Type {$ticketTypeId}, Qty {$quantity}: Matched Tier (min:{$minSoldTier}, max:" . ($maxSoldTier == PHP_INT_MAX ? 'Inf' : $maxSoldTier) . "). Units in tier: {$unitsInThisTierFromCurrentOrder}, Comm/Unit: {$commissionAmountPerUnitInTier}");
            }
        }

        Log::info("Order {$ticketOrderId}, Item Type {$ticketTypeId}, Qty {$quantity}: Total Item Commission Calculated: {$commission} using rules from " . $orderCreatedAtDate->format('Y-m-d H:i:s'));
        return $commission;
    }

	public function hasRole($roles)
	{
	    return in_array($this->role, (array) $roles);
	}

	public function isAdmin()
	{
	    return $this->hasRole(['admin', 'superadmin']);
	}
}
