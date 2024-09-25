<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Resolve Notification Message
    |--------------------------------------------------------------------------
    |
    | If this is enabled, the Logger will try to resolve the built message
    | out of the notification. This is useful if you want to debug your
    | sent notifications.
    |
    */

    'resolve-notification-message' => env('NOTIFICATION_LOG_RESOLVE_NOTIFICATION_MESSAGE', false),

    /*
    |--------------------------------------------------------------------------
    | Prune After Days
    |--------------------------------------------------------------------------
    |
    | Defines the number of days after which old log entries will be pruned
    | from the database to manage the size of the log table.
    |
    */

    'prune_after_days' => 180,

    /*
    |--------------------------------------------------------------------------
    | Log All By Default
    |--------------------------------------------------------------------------
    |
    | If enabled, all notifications will be logged by default unless explicitly
    | excluded. This can help ensure that all notifications are tracked for
    | debugging and auditing purposes.
    |
    */

    'log_all_by_default' => false,
];
