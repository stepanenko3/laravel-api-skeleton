<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Query\Builder;

trait GeoLocation
{
    public function initializeGeoLocation(): void
    {
        static::retrieved(function ($model): void {
            // $model->spatial = array_merge($model->spatial ?? [], ['coordinates']);
        });
    }

    public function getDistance(): string
    {
        $distance = null;
        $suffix = null;

        if ($this->distance > 10) {
            $distance = round($this->distance);
            $suffix = 'km';
        } elseif ($this->disnace > 1) {
            //
        } else {
            $distance = round($this->distance * 1000);
            $suffix = 'm';
        }

        return number($distance) . trans('app.distance.' . $suffix);
    }

    public function coordinates(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return;
                }

                try {
                    return unpack(
                        format: 'x/x/x/x/corder/Ltype/dlat/dlng',
                        string: (string) $value,
                    );
                } catch (Exception $e) {
                    return;
                }
            },
        );
    }

    public function scopeWhereDistance(
        Builder $query,
        int | float $lat,
        int | float $lng,
        int | float $radius,
    ): Builder {
        return $query->whereRaw(
            sql: $this->distanceCalculate() . ' < ?',
            bindings: [
                $lat,
                $lng,
                $lat,
                $radius,
            ]
        );
    }

    public function scopeWithDistance(
        Builder $query,
        int | float $lat,
        int | float $lng,
    ): Builder {
        return $query->selectRaw(
            expression: $this->distanceCalculate() . ' as distance',
            bindings: [
                $lat,
                $lng,
                $lat,
            ]
        );
    }

    public function scopeOrderByDistance(
        Builder $query,
        int | float $lat,
        int | float $lng,
        string $direction = 'ASC',
    ) {
        return $query->orderByRaw(
            sql: $this->distanceCalculate() . ' ' . $direction,
            bindings: [
                $lat,
                $lng,
                $lat,
            ],
        );
    }

    public function distanceCalculate(): string
    {
        return '(6371 * acos(cos(radians(?)) * cos(radians(ST_X(`coordinates`))) * cos(radians(ST_Y(`coordinates`)) - radians(?)) + sin(radians(?)) * sin(radians(ST_X(`coordinates`)))))';
    }
}
