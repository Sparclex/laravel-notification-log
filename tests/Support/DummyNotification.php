<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Models\Concerns\LogsNotifications;
use Ramsey\Uuid\Uuid;

class DummyNotification extends Notification implements ShouldLogNotification
{
    use LogsNotifications;

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

    public function fingerprint($notifiable)
    {
        return "dummy-fingerprint-{$this->id}";
    }

    public function getExtraData(): array
    {
        return [
            'extra' => 'data',
        ];
    }
}
