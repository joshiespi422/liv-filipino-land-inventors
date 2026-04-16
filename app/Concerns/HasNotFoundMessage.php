<?php

namespace App\Concerns;

trait HasNotFoundMessage
{
    public static function notFoundMessage(): string
    {
        return class_basename(static::class) . ' not found.';
    }
}
