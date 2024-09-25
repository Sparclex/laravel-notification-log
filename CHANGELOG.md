# Changelog

All notable changes to `laravel-notification-log` will be documented in this file.

## 4.0.0 - 2024-09-25

### What's Changed

- Deprecated plain mail message logging; now only logging notifications.
- Notifications sending mail messages now log the mail content and metadata.
- Removed mail body compression—messages are now stored in plain text.
- Introduced `getExtraData` method for notifications to enrich data when sent.
- Updated to Pest 3.

### Upgrade from 3.0.0 to 4.0.0

Before upgrading, **ensure you create a database backup!**

We’ve added a migration that handles all schema changes and attempts to migrate logged mail messages, merging them with the associated notification log entries.

**Important**: This migration **drops** the `notification_logs_sent_messages` table. If you want to keep this table, remove the line `Schema::drop('notification_logs_sent_messages');` in the migration.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog;

return new class extends Migration {
    public function up()
    {
        Schema::table('notification_logs_sent_notifications', function (Blueprint $table) {
            $table->dateTime('sent_at')->nullable()->after('attempt');
            $table->longText('message')->nullable()->change();
        });

        DB::table('notification_logs_sent_messages')->get()->each(function ($messageLog) {
            $mailableId = $this->fromJson($messageLog->mailable)[1] ?? null;

            if (! $messageLog) {
                return;
            }

            $notification = SentNotificationLog::query()
                ->where('notification_id', $mailableId)
                ->first();

            if (! $notification) {
                return;
            }

            $data = [
                'to' => $this->fromJson($messageLog->to),
                'cc' => $this->fromJson($messageLog->cc),
                'bcc' => $this->fromJson($messageLog->bcc),
                'reply_to' =>  $this->fromJson($messageLog->reply_to),
                'from' => $this->fromJson($messageLog->sender),
                'subject' => $messageLog->subject,
                'attachments' => $this->fromJson($messageLog->attachments),
            ];

            SentNotificationLog::withoutTimestamps(function () use ($notification, $messageLog, $data) {
                $notification->update([
                    ...$notification->data ?? [],
                    'message' => $messageLog->body,
                    'sent_at' => $messageLog->sent_at,
                    'data' => $data,
                ]);
            });

            DB::table('notification_logs_sent_messages')
                ->where('id', $messageLog->id)
                ->delete();
        });

        Schema::drop('notification_logs_sent_messages');
    }

    protected function fromJson(?string $string): ?array
    {
        if (blank($string)) {
            return null;
        }

        $data =  json_decode($string, true, 512, JSON_THROW_ON_ERROR);

        if (blank($data)) {
            return null;
        }

        return $data;
    }
};


```
**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/compare/3.0.0...4.0.0

## 3.0.0 - 2024-07-17

### What's Changed

- Use NotificationFailed event of Laravel and change response handling
- Add support for Twilio and Slack channels
- Handle channels that fire NotificationFailed events

**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/compare/2.1.0...3.0.0

## 2.1.0 - 2024-07-17

### What's Changed

* Add ensure unique notification interface by @Sparclex in https://github.com/okaufmann/laravel-notification-log/pull/1
* Log skip unique notification  by @Sparclex in https://github.com/okaufmann/laravel-notification-log/pull/2

### New Contributors

* @Sparclex made their first contribution in https://github.com/okaufmann/laravel-notification-log/pull/1

**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/compare/2.0.2...2.1.0

## 2.0.2 - 2024-07-16

**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/compare/2.0.1...2.0.2

## 2.0.1 - 2024-07-16

**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/compare/2.0.0...2.0.1

## 2.0.0 - 2024-07-16

**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/compare/1.0.0...2.0.0

## 1.0.0 - 2024-07-05

**Full Changelog**: https://github.com/okaufmann/laravel-notification-log/commits/1.0.0
