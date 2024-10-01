<?php

namespace Okaufmann\LaravelNotificationLog\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog;

/** @mixin Model $this */
trait HasNotifiableHistory
{
    public function latestLoggedNotification(
        ?string $fingerprint = null,
        string|array|null $notificationTypes = null,
        ?Carbon $before = null,
        ?Carbon $after = null,
        string|array|null $channel = null,
    ): ?SentNotificationLog {
        return $this->latestLoggedNotificationQuery(...func_get_args())->first();
    }

    public function latestLoggedNotificationQuery(
        ?string $fingerprint = null,
        string|array|null $notificationTypes = null,
        ?Carbon $before = null,
        ?Carbon $after = null,
        string|array|null $channel = null,
    ): Builder {
        return $this->getNotificationModelType()::latestForQuery(
            $this,
            ...func_get_args(),
        );
    }

    public function notificationLogItems(): MorphMany
    {
        return $this->morphMany($this->getNotificationModelType(), 'notifiable')
            ->orderByDesc('created_at')
            ->orderByDesc($this->getKeyName());
    }

    protected function getNotificationModelType(): string
    {
        return config('notification-log.model');
    }
}
