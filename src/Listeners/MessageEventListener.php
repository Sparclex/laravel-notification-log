<?php

namespace Okaufmann\LaravelNotificationLog\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Okaufmann\LaravelNotificationLog\Events\NotificationFailed;
use Okaufmann\LaravelNotificationLog\Loggers\SentMessageLogger;
use Okaufmann\LaravelNotificationLog\Loggers\SentNotificationLogger;

class MessageEventListener
{
    public function handleSentNotification(NotificationSent $event)
    {
        resolve(SentNotificationLogger::class)->logSentNotification($event);
    }

    public function handleSendingNotification(NotificationSending $event)
    {
        resolve(SentNotificationLogger::class)->logSendingNotification($event);
    }

    public function handleFailedNotification(NotificationFailed $event)
    {
        resolve(SentNotificationLogger::class)->logFailedNotification($event);
    }

    public function handleSentMail(MessageSent $event)
    {
        resolve(SentMessageLogger::class)->logSentMessage($event);
    }

    public function subscribe($events)
    {
        $events->listen(NotificationSent::class, [self::class, 'handleSentNotification']);
        $events->listen(NotificationSending::class, [self::class, 'handleSendingNotification']);
        $events->listen(NotificationFailed::class, [self::class, 'handleFailedNotification']);
        $events->listen(MessageSent::class, [self::class, 'handleSentMail']);
    }
}
