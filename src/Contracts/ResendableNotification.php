<?php

namespace Okaufmann\LaravelNotificationLog\Contracts;

interface ResendableNotification
{
    public function isBeingResent(): bool;

    public function markAsResending(): static;
}
