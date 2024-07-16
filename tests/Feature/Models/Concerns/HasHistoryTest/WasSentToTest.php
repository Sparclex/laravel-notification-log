<?php

use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog as NotificationLogItem;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotificationWithHistory;
use Okaufmann\LaravelNotificationLog\Tests\Support\TestUser as User;
use Spatie\TestTime\TestTime;

beforeEach(function () {
    TestTime::freeze();

    $this->notifiable = User::factory()->create();
});

it('can determine if it was sent in the past hour', function (
    int $createdMinutesAgo,
    bool $expectedResult,
) {
    NotificationLogItem::factory()
        ->forNotifiable($this->notifiable)
        ->create([
            'notification_type' => DummyNotificationWithHistory::class,
            'created_at' => now()->subMinutes($createdMinutesAgo),
        ]);

    $hasHistoryCalls = function ($notifiable) {
        return $this
            ->wasSentTo($notifiable)
            ->inThePastMinutes(60);
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBe($expectedResult);
})->with([
    [59, true],
    [60, true],
    [61, false],
]);

it('will return false when using it for an other notifiable', function () {
    $otherNotifiable = User::factory()->create();

    NotificationLogItem::factory()
        ->forNotifiable($otherNotifiable)
        ->create([
            'notification_type' => DummyNotificationWithHistory::class,
            'created_at' => now()->subMinutes(30),
        ]);

    $hasHistoryCalls = function ($notifiable) {
        return $this
            ->wasSentTo($notifiable)
            ->inThePastMinutes(60);
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBeFalse();
});

it('will return false when using it for an other notification type', function () {
    NotificationLogItem::factory()
        ->forNotifiable($this->notifiable)
        ->create([
            'notification_type' => 'other-type',
            'created_at' => now()->subMinutes(30),
        ]);

    $hasHistoryCalls = function ($notifiable) {
        return $this
            ->wasSentTo($notifiable)
            ->inThePastMinutes(60);
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBeFalse();
});

it('can find a sent notification with the same fingerprint', function (
    ?string $fingerprint,
    bool $expectedResult,
) {
    NotificationLogItem::factory()
        ->forNotifiable($this->notifiable)
        ->create([
            'fingerprint' => $fingerprint,
            'notification_type' => DummyNotificationWithHistory::class,
            'created_at' => now()->subMinutes(30),
        ]);

    $hasHistoryCalls = function ($notifiable) {
        return $this
            ->wasSentTo($notifiable, withSameFingerprint: true)
            ->inThePastMinutes(60);
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBe($expectedResult);
})->with([
    ['dummy-fingerprint', true],
    ['other-fingerprint', false],
    [null, false],
]);

it('can find a sent notification while ignoring the fingerprint', function (
    ?string $fingerprint,
) {
    NotificationLogItem::factory()
        ->forNotifiable($this->notifiable)
        ->create([
            'fingerprint' => $fingerprint,
            'notification_type' => DummyNotificationWithHistory::class,
            'created_at' => now()->subMinutes(30),
        ]);

    $hasHistoryCalls = function ($notifiable) {
        return $this
            ->wasSentTo($notifiable)
            ->inThePastMinutes(60);
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBeTrue();
})->with([
    ['dummy-fingerprint'],
    ['other-fingerprint'],
    [null],
]);

it('can find a sent notification with the same channel', function (
    ?string $channel,
    bool $expectedResult,
) {
    NotificationLogItem::factory()
        ->forNotifiable($this->notifiable)
        ->create([
            'channel' => $channel,
            'notification_type' => DummyNotificationWithHistory::class,
            'created_at' => now()->subMinutes(30),
        ]);

    $hasHistoryCalls = function ($notifiable, $channel) {
        return $this
            ->wasSentTo($notifiable)
            ->onChannel($channel)
            ->inThePastMinutes(60);
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBe($expectedResult);
})->with([
    ['mail', true],
    ['other-channel', false],
]);

it('can determine if it was sent in the past', function (
    bool $created,
    bool $expectedResult,
) {
    if ($created) {
        NotificationLogItem::factory()
            ->forNotifiable($this->notifiable)
            ->create([
                'notification_type' => DummyNotificationWithHistory::class,
                'created_at' => now(),
            ]);
    }

    $hasHistoryCalls = function ($notifiable) {
        return $this
            ->wasSentTo($notifiable)
            ->inThePast();
    };

    expect(executeInNotification($hasHistoryCalls, $this->notifiable))
        ->toBe($expectedResult);
})->with([
    [true, true],
    [false, false],
]);

function executeInNotification(Closure $closure, User $notifiable): bool
{
    $closure = Closure::bind($closure, new DummyNotificationWithHistory());

    DummyNotificationWithHistory::setHistoryTestCallable($closure);

    return (new DummyNotificationWithHistory())->historyTest($notifiable);
}
