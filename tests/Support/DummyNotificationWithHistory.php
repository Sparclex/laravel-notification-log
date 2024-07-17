<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Closure;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Okaufmann\LaravelNotificationLog\Contracts\EnsureUniqueNotification;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Models\Concerns\HasHistory;
use Okaufmann\LaravelNotificationLog\Models\Concerns\LogsNotifications;

class DummyNotificationWithHistory extends Notification implements EnsureUniqueNotification, ShouldLogNotification
{
    use HasHistory;
    use LogsNotifications;

    protected static Closure $historyTestCallable;

    public static function setHistoryTestCallable(callable $historyTestCallable)
    {
        self::$historyTestCallable = $historyTestCallable;
    }

    public function historyTest($notifiable): bool
    {
        return app()->call(self::$historyTestCallable, [
            'notifiable' => $notifiable,
            'channel' => Arr::first($this->via($notifiable)),
        ]);
    }

    public function fingerprint($notifiable): string
    {
        return 'dummy-fingerprint';
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
