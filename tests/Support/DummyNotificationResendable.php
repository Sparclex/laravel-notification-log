<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Notifications\Notification;
use Okaufmann\LaravelNotificationLog\Contracts\ResendableNotification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Models\Concerns\HandlesResending;
use Okaufmann\LaravelNotificationLog\Models\Concerns\LogsNotifications;

class DummyNotificationResendable extends Notification implements ResendableNotification, ShouldLogNotification
{
    use HandlesResending;
    use LogsNotifications;

    public function fingerprint($notifiable): string
    {
        return 'dummy-fingerprint';
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
