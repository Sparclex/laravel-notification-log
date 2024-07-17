<?php

namespace Okaufmann\LaravelNotificationLog\Loggers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Okaufmann\LaravelNotificationLog\Contracts\ShouldLogNotification;
use Okaufmann\LaravelNotificationLog\Events\NotificationFailed;
use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog;
use Okaufmann\LaravelNotificationLog\NotificationDeliveryStatus;

class NotificationLogger
{
    public function logSkippedNotification(NotificationSending $event): ?SentNotificationLog
    {
        if (! $event->notification instanceof ShouldLogNotification) {
            return null;
        }

        $this->increaseNotificationAttempt($event);

        /** @var SentNotificationLog $notification */
        $notification = SentNotificationLog::updateOrCreate([
            'notification_id' => $this->getNotificationId($event->notification),
            'notification_type' => $this->getNotificationType($event),
            'channel' => $event->channel,
            'attempt' => $event->notification->getCurrentAttempt(),
        ], [
            'notifiable_type' => $this->getNotifiableType($event),
            'notifiable_id' => $this->getNotifiableKey($event),
            'anonymous_notifiable_routes' => $this->getAnonymousRoutes($event),
            'fingerprint' => $this->getFingerprintForNotification($event->notification, $event->notifiable),
            'queued' => in_array(ShouldQueue::class, class_implements($event->notification)),
            'message' => $this->resolveMessage($event->channel, $event->notification, $event->notifiable),
            'status' => NotificationDeliveryStatus::SKIPPED,
        ]);

        return $notification;
    }

    public function logSendingNotification(NotificationSending $event): ?SentNotificationLog
    {
        if (! $event->notification instanceof ShouldLogNotification) {
            return null;
        }

        $this->increaseNotificationAttempt($event);

        /** @var SentNotificationLog $notification */
        $notification = SentNotificationLog::updateOrCreate([
            'notification_id' => $this->getNotificationId($event->notification),
            'notification_type' => $this->getNotificationType($event),
            'channel' => $event->channel,
            'attempt' => $event->notification->getCurrentAttempt(),
        ], [
            'notifiable_type' => $this->getNotifiableType($event),
            'notifiable_id' => $this->getNotifiableKey($event),
            'anonymous_notifiable_routes' => $this->getAnonymousRoutes($event),
            'fingerprint' => $this->getFingerprintForNotification($event->notification, $event->notifiable),
            'queued' => in_array(ShouldQueue::class, class_implements($event->notification)),
            'message' => $this->resolveMessage($event->channel, $event->notification, $event->notifiable),
            'status' => NotificationDeliveryStatus::SENDING,
        ]);

        return $notification;
    }

    public function logSentNotification(NotificationSent $event): ?SentNotificationLog
    {
        if (! $event->notification instanceof ShouldLogNotification) {
            return null;
        }

        /** @var SentNotificationLog $notification */
        $notification = SentNotificationLog::updateOrCreate([
            'notification_id' => $this->getNotificationId($event->notification),
            'notification_type' => $this->getNotificationType($event),
            'channel' => $event->channel,
            'attempt' => $event->notification->getCurrentAttempt(),
        ], [
            'response' => $this->formatResponse($event->response),
            'status' => NotificationDeliveryStatus::SENT,
        ]);

        return $notification;
    }

    public function logFailedNotification(NotificationFailed $event): ?SentNotificationLog
    {
        if (! $event->notification instanceof ShouldLogNotification) {
            return null;
        }

        /** @var SentNotificationLog $notification */
        $notification = SentNotificationLog::updateOrCreate([
            'notification_id' => $this->getNotificationId($event->notification),
            'notification_type' => $this->getNotificationType($event),
            'channel' => $event->channel,
            'attempt' => $event->notification->getCurrentAttempt(),
        ], [
            'response' => $event->exception,
            'status' => NotificationDeliveryStatus::FAILED,
        ]);

        return $notification;
    }

    public function resolveMessage(string $channel, Notification $notification, $notifiable)
    {
        if (! config('notification-log.resolve-notification-message')) {
            return null;
        }

        $channelManager = resolve(ChannelManager::class);
        $channel = $channelManager->driver($channel);

        // we never want to save the mail message here, as it will be logged by the mail logger.
        if ($channel instanceof MailChannel) {
            return null;
        }

        try {
            if ($channel instanceof \Illuminate\Notifications\Channels\VonageSmsChannel) {
                $message = $notification->toVonage($notifiable);

                if (is_string($message)) {
                    $message = new \Illuminate\Notifications\Messages\VonageMessage($message);
                }

                return $message->content;
            }

            if ($channel instanceof \NotificationChannels\Twilio\TwilioChannel) {
                $message = $notification->toTwilio($notifiable);

                if (is_string($message)) {
                    $message = new \NotificationChannels\Twilio\TwilioSmsMessage($message);
                }

                return $message->content;
            }

            if ($channel instanceof \NotificationChannels\Telegram\TelegramChannel) {
                $message = $notification->toTelegram($notifiable);
                if (is_string($message)) {
                    $message = \NotificationChannels\Telegram\TelegramMessage::create($message);
                }

                return $message->toArray();
            }

            if ($channel instanceof \NotificationChannels\WebPush\WebPushChannel) {
                $message = $notification->toWebPush($notifiable, $notification);

                return $message->toArray();
            }

            if ($channel instanceof DatabaseChannel) {
                if (method_exists($notification, 'toDatabase')) {
                    return is_array($data = $notification->toDatabase($notifiable))
                        ? $data : null;
                }

                if (method_exists($notification, 'toArray')) {
                    return $notification->toArray($notifiable);
                }
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getFingerprintForNotification(Notification $notification, $notifiable)
    {
        if (method_exists($notification, 'fingerprint')) {
            return $notification->fingerprint($notifiable);
        }

        return null;
    }

    protected function getNotifiableType(NotificationSending $event): ?string
    {
        /** @var Model|AnonymousNotifiable $notifiable */
        $notifiable = $event->notifiable;

        return $notifiable instanceof Model
            ? $notifiable->getMorphClass()
            : null;
    }

    protected function getNotifiableKey(NotificationSending $event): mixed
    {
        /** @var Model|AnonymousNotifiable $notifiable */
        $notifiable = $event->notifiable;

        return $notifiable instanceof Model
            ? $notifiable->getKey()
            : null;
    }

    protected function getNotificationType(NotificationSending|NotificationSent|NotificationFailed $event): string
    {
        $notification = $event->notification;

        return $this->getNotificationTypeForNotification($notification, $event->notifiable);
    }

    public function getNotificationTypeForNotification(Notification $notification, $notifiable)
    {
        if (method_exists($notification, 'logType')) {
            return $notification->logType($notifiable);
        }

        return get_class($notification);
    }

    protected function getAnonymousRoutes(NotificationSending $event): ?array
    {
        if (! $event->notifiable instanceof AnonymousNotifiable) {
            return null;
        }

        return $event->notifiable->routes;
    }

    /**
     * Format the given notifiable into a tag.
     *
     * @param  mixed  $notifiable
     */
    protected function formatNotifiable($notifiable): string
    {
        if ($notifiable instanceof Model) {
            return get_class($notifiable).':'.implode('_', Arr::wrap($notifiable->getKey()));
        }

        if ($notifiable instanceof AnonymousNotifiable) {
            $routes = array_map(function ($route) {
                return is_array($route) ? implode(',', $route) : $route;
            }, $notifiable->routes);

            return 'Anonymous:'.implode(',', $routes);
        }

        return get_class($notifiable);
    }

    protected function formatResponse($response): mixed
    {
        if (is_string($response)) {
            return $response;
        }

        if (is_object($response) && method_exists($response, 'toArray')) {
            return json_encode($response->toArray(), JSON_THROW_ON_ERROR);
        }

        return json_encode($response, JSON_THROW_ON_ERROR);
    }

    protected function getNotificationId(Notification $notification): string
    {
        if (! $notification->id) {
            $notification->id = Str::uuid()->toString();
        }

        return $notification->id;
    }

    private function increaseNotificationAttempt(NotificationSending $event): void
    {
        assert($event->notification instanceof ShouldLogNotification);

        $currentAttempt = SentNotificationLog::query()
            ->where('notification_id', $this->getNotificationId($event->notification))
            ->where('channel', $event->channel)
            ->max('attempt');

        // when you retry a job after it failed after several tries, the attempt of the instance will be reset as
        // it will be pushed as a new instance with the same notification id to the queue.
        // Therefor we need to increment the attempt manually by looking it up in the logs table.
        if ($currentAttempt > $event->notification->getCurrentAttempt()) {
            $event->notification->setCurrentAttempt($currentAttempt + 1);
        } else {
            $event->notification->setCurrentAttempt();
        }
    }
}
