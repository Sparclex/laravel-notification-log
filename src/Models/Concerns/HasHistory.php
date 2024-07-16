<?php

namespace Okaufmann\LaravelNotificationLog\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Database\NotificationHistoryQueryBuilder;
use Okaufmann\LaravelNotificationLog\Exceptions\InvalidNotifiable;

/**
 * @mixin Notification $this
 */
trait HasHistory
{
    public function wasSentTo($notifiable, $withSameFingerprint = false): NotificationHistoryQueryBuilder
    {
        $this->ensureNotifiableIsModel($notifiable);

        return new NotificationHistoryQueryBuilder(
            $this,
            $notifiable,
            shouldExist: true,
            withSameFingerprint: $withSameFingerprint
        );
    }

    public function wasNotSentTo(
        $notifiable,
        $withSameFingerprint = false
    ): NotificationHistoryQueryBuilder {
        $this->ensureNotifiableIsModel($notifiable);

        return new NotificationHistoryQueryBuilder(
            $this,
            $notifiable,
            shouldExist: false,
            withSameFingerprint: $withSameFingerprint,
        );
    }

    protected function ensureNotifiableIsModel($notifiable): void
    {
        if (! $notifiable instanceof Model) {
            throw InvalidNotifiable::shouldBeAModel();
        }

        if (! in_array(HasNotifiableHistory::class, class_uses_recursive($notifiable), true)) {
            throw InvalidNotifiable::shouldUseTrait($notifiable);
        }
    }
}
