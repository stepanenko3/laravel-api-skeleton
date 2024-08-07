<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasCreatedBy
{
    public static function createdByField(): string
    {
        return 'created_by';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model'),
            self::createdByField(),
        );
    }

    protected static function bootHasCreatedBy(): void
    {
        static::creating(
            function (Model $model): void {
                if (Auth::check()) {
                    $model->{self::createdByField()} = auth()
                        ->id();
                }
            },
        );
    }
}
