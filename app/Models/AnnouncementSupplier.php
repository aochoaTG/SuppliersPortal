<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementSupplier extends Model
{
    protected $table = 'announcement_supplier';

    protected $fillable = [
        'announcement_id',
        'supplier_id',
        'first_viewed_at',
        'last_viewed_at',
        'is_dismissed',
        'dismissed_at',
    ];

    protected $casts = [
        'is_dismissed'    => 'boolean',
        'first_viewed_at' => 'datetime',
        'last_viewed_at'  => 'datetime',
        'dismissed_at'    => 'datetime',
    ];

    /** Relationships */
    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /** Scopes */
    public function scopeViewed(Builder $q): Builder
    {
        return $q->whereNotNull('first_viewed_at');
    }

    public function scopeDismissed(Builder $q): Builder
    {
        return $q->where('is_dismissed', true)
                 ->orWhereNotNull('dismissed_at');
    }

    /** Helpers */
    public function markViewed(): void
    {
        $now = now();
        if (is_null($this->first_viewed_at)) {
            $this->first_viewed_at = $now;
        }
        $this->last_viewed_at = $now;
        $this->save();
    }

    public function dismiss(): void
    {
        $this->is_dismissed = true;
        $this->dismissed_at = now();
        $this->save();
    }

    public function getHasBeenViewedAttribute(): bool
    {
        return !is_null($this->first_viewed_at);
    }

    public function getHasBeenDismissedAttribute(): bool
    {
        return $this->is_dismissed || !is_null($this->dismissed_at);
    }
}
