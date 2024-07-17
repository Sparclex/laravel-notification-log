<?php

namespace Okaufmann\LaravelNotificationLog\Manager;

use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\NotificationSender as BaseNotificationSender;
use Override;
use Throwable;

class NotificationSender extends BaseNotificationSender
{
    /**
     * Send the given notification to the given notifiable via a channel.
     *
     * @param  mixed  $notifiable
     * @param  string  $id
     * @param  mixed  $notification
     * @param  string  $channel
     * @return void
     */
    #[Override]
    protected function sendToNotifiable($notifiable, $id, $notification, $channel)
    {
        try {
            parent::sendToNotifiable($notifiable, $id, $notification, $channel);
        } catch (Throwable $ex) {
            $this->events->dispatch(
                new NotificationFailed($notifiable, $notification, $channel, ['message' => $ex->getMessage()])
            );

            throw $ex;
        }
    }
}
