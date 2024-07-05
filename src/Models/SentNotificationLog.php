<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class SentNotificationLog extends Model
{
    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'queued' => 'boolean',
        'message' => 'json',
    ];
}
