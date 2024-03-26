<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Users;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

abstract class UserPhoneToken extends Model
{
    public const EXPIRATION_TIME = 20; // minutes

    protected $fillable = [
        'code', 'user_id', 'used',
    ];

    abstract public function sendCode(): void;

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: config('auth.providers.users.model'),
        );
    }

    public function scopeCurrentUser(Builder $query): void
    {
        $query->where('user_id', user()->id);
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
        Builder $query,
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
        Builder $query,
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
            ) > static::EXPIRATION_TIME;
    }

    public function scopeExpired(
        Builder $query,
    ): void {
        $query->where(
            column: 'created_at',
            operator: '<',
            value: now()
                ->subMinutes(
                    static::EXPIRATION_TIME,
                )
        );
    }

    public function scopeNotExpired(
        Builder $query,
    ): void {
        $query->where(
            column: 'created_at',
            operator: '>=',
            value: now()
                ->subMinutes(
                    static::EXPIRATION_TIME,
                )
        );
    }
}
