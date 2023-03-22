<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Track;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\Trackable;

class TrackerGeoip extends Model
{
    use Trackable;

    protected $connection = 'tracker';

    protected $fillable = [
        'country_code', 'country_name', 'region_code', 'region_name', 'city', 'latitude', 'longitude', 'payload',
    ];

    public function getAddress()
    {
        $res = ecollect();

        if ($this->country_name) {
            $res->push($this->country_name);
        }

        if ($this->region_name && $this->region_name !== $this->city) {
            $res->push($this->region_name);
        }

        if ($this->city) {
            $res->push($this->city);
        }

        return $res->implode(', ');
    }
}
