<?php

namespace Stepanenko3\LaravelLogicContainers\Models\Track;

use Stepanenko3\LaravelLogicContainers\Database\Eloquent\Model;
use Stepanenko3\LaravelLogicContainers\Traits\Trackable;

class TrackerGeoipCache extends Model
{
    use Trackable;

    public $timestamps = false;

    protected $connection = 'tracker';

    protected $fillable = [
        'client_ip', 'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
