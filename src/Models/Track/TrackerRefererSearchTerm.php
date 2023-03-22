<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerRefererSearchTerm extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'referer_id', 'search_term',
    ];
}
