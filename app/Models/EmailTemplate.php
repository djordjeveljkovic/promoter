<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'view_name',
        'html_content',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the currently active template (only one allowed active at a time).
     */
    public static function active(): ?self
    {
        /** @var self|null $template */
        $template = static::where('is_active', true)->first();

        return $template;
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Mark this template as the only active one in the database.
     */
    public function activate(): void
    {
        static::query()->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * Determine if this template uses a Blade view instead of inline HTML.
     */
    public function usesBladeView(): bool
    {
        return !empty($this->view_name);
    }
}
