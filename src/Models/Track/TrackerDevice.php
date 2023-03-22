<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerDevice extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'grade', 'family', 'model', 'brand', 'platform', 'platform_version', 'is_phone', 'is_tablet', 'is_desktop',
    ];
}
