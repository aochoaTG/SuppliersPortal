<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationCenterService
{
    public function queryForUser(User $user): Builder
    {
        $supplierId = $user->supplier?->id;

        return DatabaseNotification::query()
            ->where(function (Builder $query) use ($user, $supplierId) {
                $query->where(function (Builder $userQuery) use ($user) {
                    $userQuery
                        ->where('notifiable_type', User::class)
                        ->where('notifiable_id', $user->id);
                });

                if ($supplierId) {
                    $query->orWhere(function (Builder $supplierQuery) use ($supplierId) {
                        $supplierQuery
                            ->where('notifiable_type', Supplier::class)
                            ->where('notifiable_id', $supplierId);
                    });
                }
            });
    }

    public function unreadCountForUser(User $user): int
    {
        return (int) $this->queryForUser($user)
            ->whereNull('read_at')
            ->count();
    }

    public function recentForUser(User $user, int $limit = 5): Collection
    {
        return $this->queryForUser($user)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function findForUser(User $user, string $notificationId): ?DatabaseNotification
    {
        return $this->queryForUser($user)
            ->where('id', $notificationId)
            ->first();
    }

    public function markAllAsReadForUser(User $user): void
    {
        $this->queryForUser($user)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
