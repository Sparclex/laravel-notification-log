<?php

namespace Okaufmann\LaravelNotificationLog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Okaufmann\LaravelNotificationLog\Models\Concerns\CompressesBody;

/**
 * @property string $id
 * @property ?string $body
 * @property string $message_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class SentMessageLog extends Model
{
    use CompressesBody;
    use HasUlids;
    use MassPrunable;

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

    public function prunable(): Builder
    {
        $threshold = config('notification-log.prune_after_days');

        return static::where('created_at', '<=', now()->subDays($threshold));
    }
}
