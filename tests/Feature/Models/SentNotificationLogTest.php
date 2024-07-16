<?php

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;
use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog;
use Okaufmann\LaravelNotificationLog\Tests\Support\TestUser;

beforeEach(function () {
    $this->user = TestUser::factory()->create();

    $this->anotherUser = TestUser::factory()->create();
});

it('can find the latest notification for a notifiable', function () {
    expect(SentNotificationLog::latestFor($this->user))->toBeNull();

    $firstLogItem = SentNotificationLog::factory()->forNotifiable($this->user)->create();

    $secondLogItem = SentNotificationLog::factory()->forNotifiable($this->user)->create();
    $otherUserLogItem = SentNotificationLog::factory()->forNotifiable($this->anotherUser)->create();

    expect(SentNotificationLog::latestFor($this->user))->toBeModel($secondLogItem);
    expect(SentNotificationLog::latestFor($this->anotherUser))->toBeModel($otherUserLogItem);
});

it('can find the latest sent notification for a type', function () {
    $firstType1 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'notification_type' => 'type1',
    ]);

    $secondType1 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'notification_type' => 'type1',
    ]);

    $firstType2 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'notification_type' => 'type2',
    ]);

    $secondType2 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'notification_type' => 'type2',
    ]);

    expect(SentNotificationLog::latestFor($this->user, notificationType: 'type1'))
        ->toBeModel($secondType1);

    expect(SentNotificationLog::latestFor($this->user, notificationType: 'type2'))
        ->toBeModel($secondType2);

    expect(SentNotificationLog::latestFor($this->user, notificationType: 'type3'))
        ->toBeNull();
});

it('can find the latest sent notification for fingerprint', function () {
    $firstFingerprint1 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'fingerprint' => 'fingerprint-1',
    ]);

    $secondFingerprint1 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'fingerprint' => 'fingerprint-1',
    ]);

    $firstFingerprint2 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'fingerprint' => 'fingerprint-2',
    ]);

    $secondFingerprint2 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'fingerprint' => 'fingerprint-2',
    ]);

    expect(SentNotificationLog::latestFor($this->user, fingerprint: 'fingerprint-1'))
        ->toBeModel($secondFingerprint1);

    expect(SentNotificationLog::latestFor($this->user, fingerprint: 'fingerprint-2'))
        ->toBeModel($secondFingerprint2);

    expect(SentNotificationLog::latestFor($this->user, fingerprint: 'fingerprint-3'))
        ->toBeNull();
});

it('can find the latest sent notification for channel', function () {
    $firstChannel1 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'channel' => 'channel-1',
    ]);

    $secondChannel1 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'channel' => 'channel-1',
    ]);

    $firstChannel2 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'channel' => 'channel-2',
    ]);

    $secondChannel2 = SentNotificationLog::factory()->forNotifiable($this->user)->create([
        'channel' => 'channel-2',
    ]);

    expect(SentNotificationLog::latestFor($this->user, channel: 'channel-1'))
        ->toBeModel($secondChannel1);

    expect(SentNotificationLog::latestFor($this->user, channel: 'channel-2'))
        ->toBeModel($secondChannel2);

    expect(SentNotificationLog::latestFor($this->user, channel: 'channel-3'))
        ->toBeNull();
});

it('can find the latest notification in a certain period', function () {
    SentNotificationLog::factory()
        ->forNotifiable($this->user)
        ->state(new Sequence(
            ['created_at' => '2023-01-01 00:00:00'],
            ['created_at' => '2023-01-02 00:00:00'],
            ['created_at' => '2023-01-03 00:00:00'],
            ['created_at' => '2023-01-04 00:00:00'],
            ['created_at' => '2023-01-05 00:00:00'],
            ['created_at' => '2023-01-06 00:00:00'],
            ['created_at' => '2023-01-07 00:00:00'],
        ))
        ->count(7)
        ->create();

    expect(SentNotificationLog::latestFor(
        $this->user,
        before: createCarbon('2023-01-04'))
    )
        ->toHaveCreationDate('2023-01-03');

    expect(SentNotificationLog::latestFor(
        $this->user,
        after: createCarbon('2023-01-04'))
    )
        ->toHaveCreationDate('2023-01-07');

    expect(SentNotificationLog::latestFor(
        $this->user,
        before: createCarbon('2023-01-06'),
        after: createCarbon('2023-01-03'))
    )
        ->toHaveCreationDate('2023-01-05');
});

function createCarbon($date): Carbon
{
    return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
}
