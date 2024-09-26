<?php

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Okaufmann\LaravelNotificationLog\Loggers\NotificationLogger;
use Okaufmann\LaravelNotificationLog\NotificationDeliveryStatus;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyFailingNotification;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyMailNotification;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotifiable;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotification;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotificationViaTestChannel;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotificationWithExtraData;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\freezeTime;

it('can log a sending notification event', function () {
    $notifiable = new DummyNotifiable;
    $notification = new DummyNotification;

    $logger = new NotificationLogger;
    config(['notification-log.resolve_notification_message' => true]);
    $log = $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'database'));

    expect($log->notification_id)->toBe($notification->id)
        ->and($log->notification_type)->toBe(get_class($notification))
        ->and($log->notifiable_type)->toBe(get_class($notifiable))
        ->and($log->notifiable_id)->toBe($notifiable->getKey())
        ->and($log->fingerprint)->toBe('dummy-fingerprint-'.$notification->id)
        ->and($log->queued)->toBeFalse()
        ->and($log->channel)->toBe('database')
        ->and($log->message)->toBe(json_encode(['message' => 'This is just a example message.']))
        ->and($log->status)->toBe(NotificationDeliveryStatus::SENDING)
        ->and($log->attempt)->toBe(1)
        ->and($log->data)->toBe([])
        ->and($log->sent_at)->toBeNull();
});

it('can log a sending notification without message when disabled', function () {
    $notifiable = new DummyNotifiable;
    $notification = new DummyNotification;

    $logger = new NotificationLogger;
    config(['notification-log.resolve_notification_message' => false]);
    $log = $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'database'));

    expect($log->notification_id)->toBe($notification->id)
        ->and($log->notification_type)->toBe(get_class($notification))
        ->and($log->notifiable_type)->toBe(get_class($notifiable))
        ->and($log->notifiable_id)->toBe($notifiable->getKey())
        ->and($log->fingerprint)->toBe('dummy-fingerprint-'.$notification->id)
        ->and($log->queued)->toBeFalse()
        ->and($log->channel)->toBe('database')
        ->and($log->message)->toBe(null)
        ->and($log->status)->toBe(NotificationDeliveryStatus::SENDING)
        ->and($log->attempt)->toBe(1)
        ->and($log->data)->toBe([])
        ->and($log->sent_at)->toBeNull();
});

it('can update a notification once it is sent', function () {
    freezeTime();

    $notifiable = new DummyNotifiable;
    $notification = new DummyNotification;

    $logger = new NotificationLogger;
    config(['notification-log.resolve_notification_message' => true]);
    $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'database'));

    $logger->logSentNotification(new NotificationSent($notifiable, $notification, 'database', 'dummy response'));

    assertDatabaseCount('notification_logs_sent_notifications', 1);
    assertDatabaseHas('notification_logs_sent_notifications', [
        'notification_id' => $notification->id,
        'notification_type' => get_class($notification),
        'notifiable_id' => $notifiable->getKey(),
        'notifiable_type' => get_class($notifiable),
        'queued' => false,
        'channel' => 'database',
        'message' => json_encode(['message' => 'This is just a example message.']),
        'data' => json_encode(['response' => ['message' => 'dummy response']]),
        'status' => NotificationDeliveryStatus::SENT,
        'attempt' => 1,
        'sent_at' => now(),
    ]);
});

it('can log a failed notification', function () {
    $notifiable = new DummyNotifiable;
    $notification = new DummyFailingNotification;

    try {
        $notifiable->notify($notification);
    } catch (\Exception $e) {
    }

    assertDatabaseCount('notification_logs_sent_notifications', 1);
    assertDatabaseHas('notification_logs_sent_notifications', [
        'notification_id' => $notification->id,
        'notification_type' => get_class($notification),
        'notifiable_id' => $notifiable->getKey(),
        'notifiable_type' => get_class($notifiable),
        'queued' => false,
        'channel' => 'database',
        'message' => null,
        'status' => NotificationDeliveryStatus::FAILED,
        'data' => json_encode([
            'message' => 'Notification could not be sent!',
        ], JSON_THROW_ON_ERROR),
        'attempt' => 1,
        'sent_at' => null,
    ]);
});

it('does not log a failed notification twice', function () {
    $notifiable = new DummyNotifiable;
    $notification = new DummyNotificationViaTestChannel;

    try {
        $notifiable->notify($notification);
    } catch (\Exception $e) {
    }

    assertDatabaseCount('notification_logs_sent_notifications', 1);

    assertDatabaseHas('notification_logs_sent_notifications', [
        'notification_id' => $notification->id,
        'notification_type' => get_class($notification),
        'notifiable_id' => $notifiable->getKey(),
        'notifiable_type' => get_class($notifiable),
        'queued' => false,
        'channel' => 'test',
        'message' => null,
        'status' => NotificationDeliveryStatus::FAILED,
        'data' => json_encode([
            'message' => 'could not send notification!',
        ], JSON_THROW_ON_ERROR),
        'attempt' => 1,
        'sent_at' => null,
    ]);

});

it('can log a notification sent to a anonymous notifiable', function () {
    $notifiable = new AnonymousNotifiable;
    $route = fake()->safeEmail();
    $notifiable->route('mail', $route);
    $notification = new DummyMailNotification;

    $logger = new NotificationLogger;
    config(['notification-log.resolve_notification_message' => true]);
    $log = $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'mail'));

    expect($log->notification_id)->toBe($notification->id)
        ->and($log->notification_type)->toBe(DummyMailNotification::class)
        ->and($log->notifiable_type)->toBeNull()
        ->and($log->notifiable_id)->toBeNull()
        ->and($log->fingerprint)->toBe('dummy-fingerprint-'.$notification->id)
        ->and($log->anonymous_notifiable_routes)->toBe(['mail' => $route])
        ->and($log->queued)->toBeFalse()
        ->and($log->channel)->toBe('mail')
        ->and($log->message)->toMatchSnapshot()
        ->and($log->status)->toBe(NotificationDeliveryStatus::SENDING)
        ->and($log->attempt)->toBe(1)
        ->and($log->data)->toBe([
            'from' => null,
            'to' => [
                $route,
            ],
            'cc' => null,
            'bcc' => null,
            'reply_to' => null,
            'subject' => 'Dummy Notification Subject',
            'attachments' => [],
        ])
        ->and($log->sent_at)->toBeNull();
});

it('it also logs notification extra data', function () {
    $notifiable = new DummyNotifiable;
    $notification = new DummyNotificationWithExtraData;

    $logger = new NotificationLogger;
    config(['notification-log.resolve_notification_message' => true]);
    $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'database'));

    $logger->logSentNotification(new NotificationSent($notifiable, $notification, 'database', 'dummy response'));

    assertDatabaseCount('notification_logs_sent_notifications', 1);
    assertDatabaseHas('notification_logs_sent_notifications', [
        'notification_id' => $notification->id,
        'notification_type' => get_class($notification),
        'notifiable_id' => $notifiable->getKey(),
        'notifiable_type' => get_class($notifiable),
        'channel' => 'database',
        'data' => json_encode(['extra' => 'data', 'response' => ['message' => 'dummy response']]),
    ]);
});
