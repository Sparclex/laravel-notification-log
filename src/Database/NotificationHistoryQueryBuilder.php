<?php

namespace Okaufmann\LaravelNotificationLog\Database;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Loggers\NotificationLogger;

class NotificationHistoryQueryBuilder
{
    protected Builder $query;

    public function __construct(
        protected Notification $notification,
        protected Model $notifiable,
        protected bool $shouldExist,
        protected bool $withSameFingerprint,
    ) {
        $type = (new NotificationLogger)->getNotificationTypeForNotification($this->notification, $this->notifiable);

        $this->query = $this->notifiable
            ->latestLoggedNotificationQuery(
                notificationTypes: $type,
            );
    }

    public function onChannel(string $channel): static
    {
        $channel = resolve(NotificationLogger::class)->resolveChannel($channel);

        $this->query
            ->where('channel', $channel);

        return $this;
    }

    public function inThePastMinutes(?int $numberOfMinutes): bool
    {
        $query = $this->query
            ->when($numberOfMinutes !== null, function (Builder $query) use ($numberOfMinutes) {
                $query->where('created_at', '>=', now()->subMinutes($numberOfMinutes));
            })
            ->when($this->withSameFingerprint, function (Builder $query) {

                $fingerprint = resolve(NotificationLogger::class)->getFingerprintForNotification(
                    $this->notification,
                    $this->notifiable,
                );

                if ($fingerprint === null) {
                    return;
                }

                $query->where('fingerprint', $fingerprint);
            });

        return $this->shouldExist
            ? $query->exists()
            : $query->doesntExist();
    }

    public function inThePast(): bool
    {
        return $this->inThePastMinutes(null);
    }

    public function query(): Builder
    {
        return $this->query;
    }
}
