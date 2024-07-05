<?php

namespace Okaufmann\LaravelNotificationLog\Concerns;

use Okaufmann\LaravelNotificationLog\Casts\CompressedText;

trait CompressesBody
{
    public function getCasts()
    {
        if (config('notification-log.compress-messages', false)) {
            return array_merge($this->casts, [
                'body' => CompressedText::class,
            ]);
        }

        return parent::getCasts();
    }
}
