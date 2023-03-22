<?php

namespace Stepanenko3\LaravelLogicContainers\Models\Track;

use Stepanenko3\LaravelLogicContainers\Database\Eloquent\Model;
use Stepanenko3\LaravelLogicContainers\Traits\Trackable;

class TrackerReferer extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'domain_id', 'url', 'host', 'medium', 'source', 'search_term', 'search_terms_hash',
    ];
}
