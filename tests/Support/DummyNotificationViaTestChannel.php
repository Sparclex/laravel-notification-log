<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Models\Concerns\LogsNotifications;
use Ramsey\Uuid\Uuid;

class DummyNotificationViaTestChannel extends Notification implements ShouldLogNotification
{
    use LogsNotifications;

    public function __construct()
    {
        $this->id = (string) Uuid::uuid4();
    }

    public function via($notifiable)
    {
        return [TestChannel::class];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'This is just a example message.',
        ];
    }

    public function fingerprint($notifiable)
    {
        return "dummy-fingerprint-{$this->id}";
    }
}
