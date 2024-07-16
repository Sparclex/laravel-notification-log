<?php

namespace Okaufmann\LaravelNotificationLog\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NotificationFailed
{
    use Queueable, SerializesModels;

    public function __construct(
        /** @var mixed */
        public $notifiable,
        /** @var Notification */
        public $notification,
        /** @var string */
        public $channel,
        public $exception
    ) {}
}
