<?php

namespace Okaufmann\LaravelNotificationLog\Tests\Support;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as BaseUser;
use Illuminate\Notifications\Notifiable;
use Okaufmann\LaravelNotificationLog\Models\Concerns\HasNotifiableHistory;

class TestUser extends BaseUser
{
    use HasFactory;
    use HasNotifiableHistory;
    use Notifiable;
}
