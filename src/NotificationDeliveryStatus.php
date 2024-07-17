<?php

namespace Okaufmann\LaravelNotificationLog;

enum NotificationDeliveryStatus: string
{
    case SKIPPED = 'skipped';
    case SENT = 'sent';
    case SENDING = 'sending';
    case FAILED = 'failed';
}
