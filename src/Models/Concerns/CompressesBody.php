<?php

namespace Okaufmann\LaravelNotificationLog\Models\Concerns;

use Okaufmann\LaravelNotificationLog\Casts\CompressedText;

trait CompressesBody
{
    public function initializeCompressBody()
    {
        if (config('notification-log.compress-messages', false)) {
            $this->mergeCasts([
                'body' => CompressedText::class,
            ]);
        }
    }
}
