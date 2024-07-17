<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Okaufmann\LaravelNotificationLog\NotificationDeliveryStatus;

/**
 * @property string $id
 * @property string $notification_id
 * @property string $notification_type
 * @property string $notifiable_id
 * @property string $notifiable_type
 * @property array $anonymous_notifiable_routes
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
    use HasFactory;
    use HasUlids;
    use MassPrunable;

    protected $table = 'notification_logs_sent_notifications';

    protected $guarded = [];

    protected $casts = [
        'queued' => 'boolean',
        'message' => 'json',
        'anonymous_notifiable_routes' => 'array',
        'status' => NotificationDeliveryStatus::class,
    ];

    public function prunable(): Builder
    {
        $threshold = config('notification-log.prune_after_days');

        return static::where('created_at', '<=', now()->subDays($threshold));
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo('notifiable');
    }

    public static function latestFor(
        $notifiable,
        ?string $fingerprint = null,
        string|array|null $notificationType = null,
        ?Carbon $before = null,
        ?Carbon $after = null,
        string|array|null $channel = null,
    ): ?self {
        return self::latestForQuery(...func_get_args())->first();
    }

    public static function latestForQuery(
        $notifiable,
        ?string $fingerprint = null,
        string|array|null $notificationType = null,
        ?Carbon $before = null,
        ?Carbon $after = null,
        string|array|null $channel = null,
    ): Builder {
        return self::query()
            ->where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey())
            ->when($fingerprint, fn (Builder $query) => $query->where('fingerprint', $fingerprint))
            ->when($notificationType, function (Builder $query) use ($notificationType) {
                $query->whereIn('notification_type', Arr::wrap($notificationType));
            })
            ->when($channel, function (Builder $query) use ($channel) {
                $query->whereIn('channel', Arr::wrap($channel));
            })
            ->when($before, function (Builder $query) use ($before) {
                $query->where('created_at', '<', $before->toDateTimeString());
            })
            ->when($after, function (Builder $query) use ($after) {
                $query->where('created_at', '>', $after->toDateTimeString());
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
