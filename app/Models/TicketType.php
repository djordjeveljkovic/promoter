<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    protected $fillable = [
        'name',
        'price',
        'photo_path',
        'qr_coordinates',
        'is_active',
    ];

    protected $casts = [
        'qr_coordinates' => 'array',
        // Cast to bool so `->is_active` reads cleanly in Blade and
        // `$model->is_active = 1` saves correctly as 1/0 in MySQL.
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(TicketCommission::class);
    }

    /**
     * Filter scope: only ticket types available for new orders.
     * Used in OrderController / SubPromoterController so promoters
     * can never accidentally sell a deactivated type.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter scope: only deactivated types (for the admin list view).
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function getCommissionForSoldCount(int $soldCount): float
    {
        $threshold = $this->commissions()
            ->where('min_sold', '<=', $soldCount)
            ->where(function ($query) use ($soldCount) {
                $query->where('max_sold', '>', $soldCount)
                    ->orWhereNull('max_sold');
            })
            ->orderBy('min_sold', 'desc')
            ->first();

        return $threshold ? $threshold->commission_amount : 0.0;
    }
}
