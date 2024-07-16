<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Okaufmann\LaravelNotificationLog\Concerns\CompressesBody;

/**
 * @property string $id
 * @property ?string $body
 * @property string $message_id
 */
class SentMessageLog extends Model
{
    use CompressesBody;
    use HasUlids;

    protected $table = 'notification_logs_sent_messages';

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
