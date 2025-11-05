<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    use SoftDeletes;

    // Constantes para Prioridad
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_URGENT = 4;

    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'description',
        'cover_path',
        'published_at',
        'visible_until',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'published_at'  => 'datetime',
        'visible_until' => 'datetime',
        'priority'      => 'integer',
    ];

    /** Relationships */
    public function views()
    {
        return $this->hasMany(AnnouncementSupplier::class, 'announcement_id');
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'announcement_supplier', 'announcement_id', 'supplier_id')
                    ->withTimestamps()
                    ->withPivot(['first_viewed_at','last_viewed_at','is_dismissed','dismissed_at']);
    }

    /** Query scopes */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('published_at', '<=', now());
    }

    public function scopeVisible(Builder $q): Builder
    {
        return $q->where(function ($qq) {
            $qq->whereNull('visible_until')
               ->orWhere('visible_until', '>=', now());
        });
    }

    public function scopeReadyToShow(Builder $q): Builder
    {
        return $q->where('is_active', true)->published()->visible();
    }

    // Scopes para Prioridad
    public function scopePriority(Builder $q, int $priority): Builder
    {
        return $q->where('priority', $priority);
    }

    public function scopeHighPriority(Builder $q): Builder
    {
        return $q->where('priority', '>=', self::PRIORITY_HIGH);
    }

    // Announcements not dismissed for a given supplier
    public function scopeForSupplier(Builder $q, int $supplierId): Builder
    {
        return $q->whereDoesntHave('views', function ($rel) use ($supplierId) {
            $rel->where('supplier_id', $supplierId)
                ->where(function ($rr) {
                    $rr->where('is_dismissed', true)
                       ->orWhereNotNull('dismissed_at');
                });
        });
    }

    /** Accessors / Helpers */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at && $this->published_at->lte(now());
    }

    public function getIsCurrentlyVisibleAttribute(): bool
    {
        return is_null($this->visible_until) || $this->visible_until->gte(now());
    }

    public function getShouldDisplayAttribute(): bool
    {
        return (bool) ($this->is_active && $this->is_published && $this->is_currently_visible);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function getDaysLeftAttribute(): ?int
    {
        if (is_null($this->visible_until)) return null;
        return now()->diffInDays($this->visible_until, false);
    }

    // Accessors para Prioridad
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Desconocida'
        };
    }

    public function getPriorityClassAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'secondary',
            self::PRIORITY_NORMAL => 'primary',
            self::PRIORITY_HIGH => 'warning',
            self::PRIORITY_URGENT => 'danger',
            default => 'secondary'
        };
    }

    /** Actions */
    public function markViewedBy(int $supplierId): AnnouncementSupplier
    {
        /** @var AnnouncementSupplier $pivot */
        $pivot = AnnouncementSupplier::firstOrCreate(
            ['announcement_id' => $this->id, 'supplier_id' => $supplierId],
            ['first_viewed_at' => now(), 'last_viewed_at' => now()]
        );

        if ($pivot->exists && !$pivot->wasRecentlyCreated) {
            $pivot->last_viewed_at = now();
            if (is_null($pivot->first_viewed_at)) {
                $pivot->first_viewed_at = now();
            }
            $pivot->save();
        }

        return $pivot;
    }

    public function dismissFor(int $supplierId): AnnouncementSupplier
    {
        $pivot = AnnouncementSupplier::firstOrCreate(
            ['announcement_id' => $this->id, 'supplier_id' => $supplierId]
        );

        $pivot->is_dismissed = true;
        $pivot->dismissed_at = now();
        $pivot->save();

        return $pivot;
    }

    public function isDismissedFor(int $supplierId): bool
    {
        return $this->views()
            ->where('supplier_id', $supplierId)
            ->where(function ($q) {
                $q->where('is_dismissed', true)
                  ->orWhereNotNull('dismissed_at');
            })
            ->exists();
    }

    protected static function booted(): void
    {
        // optional: delete cover file on force delete
        static::deleting(function (self $model) {
            if ($model->isForceDeleting() && $model->cover_path) {
                Storage::disk('public')->delete($model->cover_path);
            }
        });
    }

    /** Métodos estáticos para opciones */
    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Baja',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
        ];
    }
}
