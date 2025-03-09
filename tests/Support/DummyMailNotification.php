<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Models\Concerns\LogsNotifications;
use Ramsey\Uuid\Uuid;

class DummyMailNotification extends Notification implements ShouldLogNotification
{
    use LogsNotifications;

    public function __construct()
    {
        $this->id = (string) Uuid::uuid4();
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Dummy Notification Subject')
            ->text('dummy-text-message');
    }

    public function fingerprint($notifiable)
    {
        return "dummy-fingerprint-{$this->id}";
    }
}
