<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Users;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

abstract class UserPhoneToken extends Model
{
    protected $fillable = [
        'code', 'user_id', 'used',
    ];

    abstract public function sendCode(): void;

    public function expirationTime(): int | float
    {
        return 20;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: config('auth.providers.users.model'),
        );
    }

    public function scopeCurrentUser(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query->where(
            column: 'user_id',
            operator: '=',
            value: user()->id,
        );
    }

    public function generateCode(
        int $codeLength = 5,
    ): int {
        $min = 10 ** ($codeLength - 1);
        $max = $min * 10 - 1;

        return random_int(
            min: $min,
            max: $max,
        );
    }

    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public function isNotValid(): bool
    {
        return $this->isUsed() || $this->isExpired();
    }

    public function scopeValid(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query
            ->notExpired()
            ->where(
                column: 'used',
                operator: '!=',
                value: true,
            );
    }

    public function scopeNotValid(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query
            ->expired()
            ->orWhere(
                column: 'used',
                operator: '=',
                value: true,
            );
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function isExpired(): bool
    {
        return $this->created_at
            ->diffInMinutes(
                now()
            ) > $this->expirationTime();
    }

    public function scopeExpired(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query->where(
            column: 'created_at',
            operator: '<',
            value: now()
                ->subMinutes(
                    $this->expirationTime(),
                )
        );
    }

    public function scopeNotExpired(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query->where(
            column: 'created_at',
            operator: '>=',
            value: now()
                ->subMinutes(
                    $this->expirationTime(),
                )
        );
    }
}
