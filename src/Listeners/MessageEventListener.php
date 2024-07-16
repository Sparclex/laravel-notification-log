<?php

namespace Okaufmann\LaravelNotificationLog\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Okaufmann\LaravelNotificationLog\Events\NotificationFailed;
use Okaufmann\LaravelNotificationLog\Loggers\MessageLogger;
use Okaufmann\LaravelNotificationLog\Loggers\NotificationLogger;

class MessageEventListener
{
    public function handleSentNotification(NotificationSent $event)
    {
        resolve(NotificationLogger::class)->logSentNotification($event);
    }

    public function handleSendingNotification(NotificationSending $event)
    {
        resolve(NotificationLogger::class)->logSendingNotification($event);
    }

    public function handleFailedNotification(NotificationFailed $event)
    {
        resolve(NotificationLogger::class)->logFailedNotification($event);
    }

    public function handleSentMail(MessageSent $event)
    {
        resolve(MessageLogger::class)->logSentMessage($event);
    }

    public function subscribe($events)
    {
        $events->listen(NotificationSent::class, [self::class, 'handleSentNotification']);
        $events->listen(NotificationSending::class, [self::class, 'handleSendingNotification']);
        $events->listen(NotificationFailed::class, [self::class, 'handleFailedNotification']);
        $events->listen(MessageSent::class, [self::class, 'handleSentMail']);
    }
}
