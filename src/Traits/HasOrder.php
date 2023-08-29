<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasOrder
{
    public static function updateSort(array $sort): void
    {
        $field = self::orderField();

        foreach ($sort as $order => $modelId) {
            self::find($modelId)->update([
                $field => $order,
            ]);
        }
    }

    public static function orderField(): string
    {
        return 'order_column';
    }

    protected static function bootHasOrder(): void
    {
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy(self::orderField(), 'asc');
        });
    }
}
