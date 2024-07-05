<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Okaufmann\LaravelNotificationLog\Concerns\CompressesBody;

class SentMessageLog extends Model
{
    use CompressesBody;
    use HasUlids;

    protected $guarded = [];

    protected $casts = [
        'mailable' => 'array',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'sender' => 'array',
        'reply_to' => 'array',
        'headers' => 'array',
        'attachments' => 'array',
        'sent_at' => 'datetime',
        'queued' => 'boolean',
    ];
}
