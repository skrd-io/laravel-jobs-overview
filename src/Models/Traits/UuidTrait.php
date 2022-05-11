<?php

namespace SkrdIo\JobsOverview\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait UuidTrait
{
    public static function bootUuidTrait(): void
    {
        static::creating(function (Model $model) {
            $model->{$model->getKeyName()} = (string) Str::uuid();
        });
    }

    public function initializeUuidTrait(): void
    {
        $this->incrementing = false;

        $this->keyType = 'string';
    }
}
