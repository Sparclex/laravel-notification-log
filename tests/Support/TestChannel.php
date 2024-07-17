<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;

class TestChannel
{
    private Dispatcher $events;

    public function __construct(Dispatcher $events)
    {
        $this->events = $events;
    }

    public function send($notifiable, Notification $notification)
    {
        $event = new NotificationFailed(
            $notifiable,
            $notification,
            'test',
            ['message' => 'could not send notification!']
        );

        $this->events->dispatch($event);

        throw new Exception('error in test channel occurred');
    }
}
