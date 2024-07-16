<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog;
use Okaufmann\LaravelNotificationLog\Tests\Support\DummyNotification;
use Okaufmann\LaravelNotificationLog\Tests\Support\TestUser;

class SentNotificationLogFactory extends Factory
{
    public $model = SentNotificationLog::class;

    public function definition(): array
    {
        $user = TestUser::factory()->create();

        return [
            'notification_id' => Str::random(12),
            'notification_type' => DummyNotification::class,
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->getKey(),
            'channel' => 'mail',
        ];
    }

    public function forNotifiable(Model $model): Factory
    {
        return $this->state(function (array $attributes) use ($model) {
            return [
                'notification_type' => $model->getMorphClass(),
                'notifiable_id' => $model->getKey(),
            ];
        });
    }
}
