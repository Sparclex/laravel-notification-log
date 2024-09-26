<?php

namespace Okaufmann\LaravelNotificationLog\Concerns;

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
