<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Models\Concerns\LogsNotifications;

class DummyFailingNotification extends Notification implements ShouldLogNotification
{
    use LogsNotifications;

    public function __construct()
    {
        $this->id = '1234567890';
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        throw new \Exception('Notification could not be sent!');
    }
}
