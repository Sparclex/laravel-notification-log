<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Concerns\LogNotification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Ramsey\Uuid\Uuid;

class DummyNotification extends Notification implements ShouldLogNotification
{
    use LogNotification;

    public function __construct()
    {
        $this->id = (string) Uuid::uuid4();
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'This is just a example message.',
        ];
    }
}
