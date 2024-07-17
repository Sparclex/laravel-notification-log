<?php

namespace Okaufmann\LaravelNotificationLog\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Okaufmann\LaravelNotificationLog\Contracts\EnsureUniqueNotification;
use Okaufmann\LaravelNotificationLog\Events\NotificationFailed;
use Okaufmann\LaravelNotificationLog\Loggers\MessageLogger;
use Okaufmann\LaravelNotificationLog\Loggers\NotificationLogger;

class MessageEventListener
{
    public function __construct(protected readonly NotificationLogger $notificationLogger, protected readonly MessageLogger $messageLogger) {}

    public function handleSentNotification(NotificationSent $event): void
    {
        $this->notificationLogger->logSentNotification($event);
    }

    public function handleSendingNotification(NotificationSending $event): ?false
    {
        if ($event->notification instanceof EnsureUniqueNotification
            && $event->notification->wasSentTo($event->notifiable, withSameFingerprint: true)->onChannel($event->channel)->inThePast()
        ) {
            $this->notificationLogger->logSkippedNotification($event);

            return false;
        }
        $this->notificationLogger->logSendingNotification($event);

        return null;
    }

    public function handleFailedNotification(NotificationFailed $event): void
    {
        $this->notificationLogger->logFailedNotification($event);
    }

    public function handleSentMail(MessageSent $event): void
    {
        $this->messageLogger->logSentMessage($event);
    }

    public function subscribe($events): void
    {
        $events->listen(NotificationSent::class, [self::class, 'handleSentNotification']);
        $events->listen(NotificationSending::class, [self::class, 'handleSendingNotification']);
        $events->listen(NotificationFailed::class, [self::class, 'handleFailedNotification']);
        $events->listen(MessageSent::class, [self::class, 'handleSentMail']);
    }
}
