<?php

namespace Stepanenko3\LaravelLogicContainers\Models\Track;

use Stepanenko3\LaravelLogicContainers\Database\Eloquent\Model;
use Stepanenko3\LaravelLogicContainers\Traits\Trackable;

class TrackerPath extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'route_id', 'path',
    ];
}
