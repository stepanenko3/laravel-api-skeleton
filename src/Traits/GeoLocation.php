<?php

namespace Stepanenko3\LaravelLogicContainers\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait GeoLocation
{
    public function initializeGeoLocation(): void
    {
        static::retrieved(function ($model): void {
            // $model->spatial = array_merge($model->spatial ?? [], ['coordinates']);
        });
    }

    public function getDistance()
    {
        if ($this->distance > 10) {
            $distance = round($this->distance);
            $suffix = 'km';
        } elseif ($this->disnace > 1) {
        } else {
            $distance = round($this->distance * 1000);
            $suffix = 'm';
        }

        return number($distance) . trans('app.distance.' . $suffix);
    }

    public function coordinates(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => unpack('x/x/x/x/corder/Ltype/dlat/dlng', $value),
        );
    }

    public function scopeWhereDistance($query, $lat, $lng, $radius)
    {
        return $query->whereRaw($this->distanceCalculate() . ' < ?', [$lat, $lng, $lat, $radius]);
    }

    public function scopeWithDistance($query, $lat, $lng)
    {
        return $query->selectRaw($this->distanceCalculate() . ' as distance', [$lat, $lng, $lat]);
    }

    public function scopeOrderByDistance($query, $lat, $lng, $direction = 'ASC')
    {
        return $query->orderByRaw(
            $this->distanceCalculate() . ' ' . $direction,
            [$lat, $lng, $lat],
        );
    }

    public function distanceCalculate()
    {
        return '(6371 * acos(cos(radians(?)) * cos(radians(ST_X(`coordinates`))) * cos(radians(ST_Y(`coordinates`)) - radians(?)) + sin(radians(?)) * sin(radians(ST_X(`coordinates`)))))';
    }
}
