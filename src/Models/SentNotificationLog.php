<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SentNotificationLog extends Model
{
    use HasUlids;
    use MassPrunable;

    protected $table = 'notification_logs_sent_notifications';

    protected $guarded = [];

    protected $casts = [
        'queued' => 'boolean',
        'message' => 'json',
        'anonymous_notifiable_routes' => 'array',
    ];

    public function prunable(): Builder
    {
        $threshold = config('notification-log.prune_after_days');

        return static::where('created_at', '<=', now()->subDays($threshold));
    }
}
