<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerEventLog extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'event_id', 'referer_id', 'session_id', 'path_id', 'route_id', 'method', 'is_ajax', 'is_secure', 'is_json', 'wants_json',
    ];

    public function event()
    {
        return $this->belongsTo('App\Models\Track\TrackerEvent');
    }

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
