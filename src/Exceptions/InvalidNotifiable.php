<?php

namespace Okaufmann\LaravelNotificationLog\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Okaufmann\LaravelNotificationLog\Models\Concerns\HasNotifiableHistory;

class InvalidNotifiable extends Exception
{
    public static function shouldBeAModel(): self
    {
        return new self('The notifiable should be a model.');
    }

    public static function shouldUseTrait(Model $model): self
    {
        $modelClass = $model::class;
        $trait = HasNotifiableHistory::class;

        return new self("The `{$modelClass}` model should use the `{$trait}`.");
    }
}
