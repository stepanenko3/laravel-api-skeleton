<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerSession extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'uuid', 'user_id', 'agent_id', 'device_id', 'referer_id', 'language_id', 'client_ip', 'is_robot', 'robot', 'last_activity', 'geoip_id', 'is_active',
    ];

    protected $casts = [
        'last_activity' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function device()
    {
        return $this->belongsTo('App\Models\Track\TrackerDevice');
    }

    public function agent()
    {
        return $this->belongsTo('App\Models\Track\TrackerAgent');
    }

    public function geoip()
    {
        return $this->belongsTo('App\Models\Track\TrackerGeoip');
    }

    public function referer()
    {
        return $this->belongsTo('App\Models\Track\TrackerReferer');
    }

    public function language()
    {
        return $this->belongsTo('App\Models\Track\TrackerLanguage');
    }

    //

    public static function findByUuid($uuid)
    {
        return self::findCached(['uuid' => $uuid]);
    }

    public function last($minutes)
    {
        return $this
            ->getSessions()
            ->where('updated_at', '>', now()->subMinutes($minutes));
    }

    public function userDevices($minutes, $user_id)
    {
        if (!$user_id) {
            return [];
        }

        $sessions = $this
            ->last($minutes)
            ->where('user_id', $user_id);

        return $sessions->get()->pluck('device')->unique();
    }

    private function getSessions()
    {
        return $this->orderBy('updated_at', 'desc');
    }
}
