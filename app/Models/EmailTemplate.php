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
        // Deactivate everyone else via the query builder so we don't have to
        // load every row into memory. We intentionally bypass model events
        // and timestamps here — flipping `is_active` is a metadata change.
        static::query()->update(['is_active' => false]);

        // Sync the in-memory state with the DB (the bulk update above did
        // not refresh `$this`) and force-write is_active=true. Using
        // forceFill + update on the query builder guarantees an UPDATE
        // even if Eloquent considers the attribute "clean" because the
        // pre-bulk-update value still matches what we want to set.
        $this->forceFill(['is_active' => true])->save();

        // Belt-and-braces: if for any reason the model save was a no-op
        // (e.g. cast + dirty-detection race in older Laravel releases),
        // issue a direct query so the DB is the source of truth.
        if (static::whereKey($this->getKey())->value('is_active') != 1) {
            static::whereKey($this->getKey())->update(['is_active' => true]);
        }
    }

    /**
     * Determine if this template uses a Blade view instead of inline HTML.
     */
    public function usesBladeView(): bool
    {
        return !empty($this->view_name);
    }
}
