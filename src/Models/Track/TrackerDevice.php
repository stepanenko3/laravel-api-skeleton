<?php

namespace Stepanenko3\LaravelLogicContainers\Models\Track;

use Stepanenko3\LaravelLogicContainers\Database\Eloquent\Model;
use Stepanenko3\LaravelLogicContainers\Traits\Trackable;

class TrackerDevice extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'grade', 'family', 'model', 'brand', 'platform', 'platform_version', 'is_phone', 'is_tablet', 'is_desktop',
    ];
}
