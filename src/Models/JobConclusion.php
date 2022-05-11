<?php

namespace SkrdIo\JobsOverview\Models;

use Illuminate\Database\Eloquent\Model;
use SkrdIo\JobsOverview\Models\Traits\UuidTrait;

class JobConclusion extends Model
{
    use UuidTrait;

    public const CREATED_AT = 'concluded_at';

    public const UPDATED_AT = null;

    protected $casts = [
        'is_fail' => 'boolean',
    ];

    protected $fillable = ['type', 'is_fail'];
}
