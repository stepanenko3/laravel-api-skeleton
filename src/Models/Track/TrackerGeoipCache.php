<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

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
