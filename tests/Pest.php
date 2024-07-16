<?php

use Illuminate\Database\Eloquent\Model;
use Okaufmann\LaravelNotificationLog\Tests\TestCase;

uses(TestCase::class)->in(__DIR__.'/Feature');

expect()->extend('toBeModel', function (?object $model) {
    if (is_null($model)) {
        return false;
    }

    if (! $model instanceof Model) {
        return false;
    }

    if (get_class($this->value) !== get_class($model)) {
        return false;
    }

    expect($this->value)->toBeInstanceOf($model::class);

    expect($this->value->id)->toBe($model->id);

    if ($this->value->id !== $model->id) {
        return false;
    }

    return true;
});

expect()->extend('toHaveCreationDate', function (string $date) {
    expect($this->value)->not()->toBeNull();

    expect($this->value->created_at->format('Y-m-d'))->toBe($date);
});
