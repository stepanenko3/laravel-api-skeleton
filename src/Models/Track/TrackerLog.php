<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerLog extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'session_id', 'referer_id', 'path_id', 'route_id', 'method', 'is_ajax', 'is_secure', 'is_json', 'wants_json',
    ];

    public function path()
    {
        return $this->belongsTo('App\Models\Track\TrackerPath');
    }

    public function route()
    {
        return $this->belongsTo('App\Models\Track\TrackerRoute');
    }

    public function referer()
    {
        return $this->belongsTo('App\Models\Track\TrackerReferer');
    }

    public function session()
    {
        return $this->belongsTo('App\Models\Track\TrackerSession');
    }
}
