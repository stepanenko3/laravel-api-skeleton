<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCreatedBy
{
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::createdByField());
    }

    public static function createdByField(): string
    {
        return 'created_by';
    }

    protected static function bootHasCreatedBy(): void
    {
        static::creating(function (Model $model): void {
            if (auth()->check()) {
                $model->{self::createdByField()} = auth()
                    ->id();
            }
        });
    }
}
