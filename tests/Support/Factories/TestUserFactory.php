<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Okaufmann\LaravelNotificationLog\Tests\Support\TestUser;

class TestUserFactory extends Factory
{
    public $model = TestUser::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ];
    }
}
