<?php

namespace Okaufmann\LaravelNotificationLog\Models\Concerns;

use Okaufmann\LaravelNotificationLog\Models\SentNotificationLog;

/**
 * @mixin SentNotificationLog
 */
trait HandlesResending
{
    protected bool $isResending = false;

    public function isBeingResent(): bool
    {
        return $this->isResending;
    }

    public function markAsResending(): static
    {
        $this->isResending = true;

        return $this;
    }
}
