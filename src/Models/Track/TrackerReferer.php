<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerReferer extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'domain_id', 'url', 'host', 'medium', 'source', 'search_term', 'search_terms_hash',
    ];
}
