<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $notification_id
 * @property string $notification_type
 * @property string $notifiable_id
 * @property string $notifiable_type
 * @property string $fingerprint
 * @property string $channel
 * @property int $attempt
 * @property array $notifiable
 * @property bool $queued
 * @property array $message
 * @property string $status
 */
class SentNotificationLog extends Model
{
    use HasUlids;
    protected $table = 'notification_logs_sent_notifications';

    protected $guarded = [];

    protected $casts = [
        'queued' => 'boolean',
        'message' => 'json',
    ];
}
