<?php

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Okaufmann\LaravelNotificationLog\Loggers\NotificationLogger;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyFailingNotification;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotifiable;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotification;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('can log a sending notification event', function () {
    $notifiable = new DummyNotifiable();
    $notification = new DummyNotification();

    $logger = new NotificationLogger();
    config(['notification-log.resolve-notification-message' => true]);
    $log = $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'database'));

    expect($log->notification_id)->toBe($notification->id)
        ->and($log->notification_type)->toBe(get_class($notification))
        ->and($log->notifiable_type)->toBe(get_class($notifiable))
        ->and($log->notifiable_id)->toBe($notifiable->getKey())
        ->and($log->fingerprint)->toBe('dummy-fingerprint-'.$notification->id)
        ->and($log->queued)->toBeFalse()
        ->and($log->channel)->toBe('database')
        ->and($log->message)->toBe(['message' => 'This is just a example message.'])
        ->and($log->status)->toBe('sending')
        ->and($log->attempt)->toBe(1);
});

it('can log a sending notification without message when disabled', function () {
    $notifiable = new DummyNotifiable();
    $notification = new DummyNotification();

    $logger = new NotificationLogger();
    config(['notification-log.resolve-notification-message' => false]);
    $log = $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'database'));

    expect($log->notification_id)->toBe($notification->id)
        ->and($log->notification_type)->toBe(get_class($notification))
        ->and($log->notifiable_type)->toBe(get_class($notifiable))
        ->and($log->notifiable_id)->toBe($notifiable->getKey())
        ->and($log->fingerprint)->toBe('dummy-fingerprint-'.$notification->id)
        ->and($log->queued)->toBeFalse()
        ->and($log->channel)->toBe('database')
        ->and($log->message)->toBe(null)
        ->and($log->status)->toBe('sending')
        ->and($log->attempt)->toBe(1);
});

it('can update a notification once it is sent', function () {
    $notifiable = new DummyNotifiable();
    $notification = new DummyNotification();

    $logger = new NotificationLogger();
    config(['notification-log.resolve-notification-message' => true]);
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
        'response' => 'dummy response',
        'status' => 'sent',
        'attempt' => 1,
    ]);
});

it('can log a failed notification', function () {
    $notifiable = new DummyNotifiable();
    $notification = new DummyFailingNotification();

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
        'status' => 'error',
        'response' => $e,
        'attempt' => 1,
    ]);
});

it('can log a notification sent to a anonymous notifiable', function () {
    $notifiable = new \Illuminate\Notifications\AnonymousNotifiable();
    $route = fake()->safeEmail();
    $notifiable->route('mail', $route);
    $notification = new DummyNotification();

    $logger = new NotificationLogger();
    config(['notification-log.resolve-notification-message' => true]);
    $log = $logger->logSendingNotification(new NotificationSending($notifiable, $notification, 'mail'));

    expect($log->notification_id)->toBe($notification->id)
        ->and($log->notification_type)->toBe(DummyNotification::class)
        ->and($log->notifiable_type)->toBeNull()
        ->and($log->notifiable_id)->toBeNull()
        ->and($log->fingerprint)->toBe('dummy-fingerprint-'.$notification->id)
        ->and($log->anonymous_notifiable_routes)->toBe(['mail' => $route])
        ->and($log->queued)->toBeFalse()
        ->and($log->channel)->toBe('mail')
        ->and($log->message)->toBeNull()
        ->and($log->status)->toBe('sending')
        ->and($log->attempt)->toBe(1);
});
