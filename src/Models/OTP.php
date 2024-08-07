<?php

namespace Stepanenko3\LaravelApiSkeleton\Models;

use Closure;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Exceptions\OTP\OtpVerifyCodeAlreadyUsed;
use Stepanenko3\LaravelApiSkeleton\Exceptions\OTP\OtpVerifyCodeExpired;
use Stepanenko3\LaravelApiSkeleton\Exceptions\OTP\OtpVerifyCodeIncorrect;

class OTP extends Model
{
    protected $table = 'otp';

    protected $fillable = [
        'code',
        'for_id',
        'for_type',
        'target',
        'type',
        'used',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    public static function generateCode(
        ?int $codeLength = null,
    ): int {
        $codeLength ??= config('api-skeleton.otp.length');

        $min = 10 ** ($codeLength - 1);
        $max = $min * 10 - 1;

        return random_int(
            min: $min,
            max: $max,
        );
    }

    public static function verify(
        string | int $code,
        string $target,
        string $type,
    ): self {
        $otp = self::query()
            ->where(
                column: 'code',
                operator: '=',
                value: $code,
            )
            ->where(
                column: 'target',
                operator: '=',
                value: $target,
            )
            ->where(
                column: 'type',
                operator: '=',
                value: $type,
            )
            ->latest()
            ->first();

        if (!$otp) {
            throw new OtpVerifyCodeIncorrect();
        }

        if ($otp->isUsed()) {
            throw new OtpVerifyCodeAlreadyUsed();
        }

        if ($otp->isExpired()) {
            throw new OtpVerifyCodeExpired();
        }

        return $otp;
    }

    public static function use(
        string | int $code,
        string $target,
        string $type,
    ): bool {
        $rateLimiterKey = self::cacheKey(
            target: $target,
            type: $type,
        );

        if (RateLimiter::tooManyAttempts(
            key: $rateLimiterKey,
            maxAttempts: $perMinute = 5,
        )) {
            throw new ThrottleRequestsException();
        }

        RateLimiter::increment(
            key: $rateLimiterKey,
        );

        self::verify(
            code: $code,
            target: $target,
            type: $type,
        )
            ->markAsUsed();

        RateLimiter::clear(
            key: $rateLimiterKey,
        );

        return true;
    }

    //

    public function for(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsUsed(): bool
    {
        return $this
            ->forceFill([
                'used' => 1,
            ])
            ->save();
    }

    public function send(
        Closure $callback,
    ): self {
        $rateLimiterKey = self::cacheKey(
            target: $this->target,
            type: $this->type,
        ) . '_send';

        if (RateLimiter::tooManyAttempts(
            key: $rateLimiterKey,
            maxAttempts: $perMinute = 5,
        )) {
            throw new ThrottleRequestsException();
        }

        RateLimiter::increment(
            key: $rateLimiterKey,
        );

        $callback($this);

        RateLimiter::clear(
            key: $rateLimiterKey,
        );

        return $this;
    }

    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public function isNotValid(): bool
    {
        return $this->isUsed() || $this->isExpired();
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function isExpired(): bool
    {
        return now()->isAfter(
            date: $this->expires_at,
        );
    }

    public function scopeCurrentUser(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query->when(
            value: Auth::check(),
            callback: fn (EloquentBuilderContract | QueryBuilderContract | Builder $builder) => $builder->whereMorphedTo(
                relation: 'for',
                model: user(),
            ),
        );
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

    public function scopeExpired(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query->where(
            column: 'expires_at',
            operator: '<',
            value: now(),
        );
    }

    public function scopeNotExpired(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
    ): void {
        $query->where(
            column: 'expires_at',
            operator: '>=',
            value: now(),
        );
    }

    private static function cacheKey(
        string $target,
        string $type,
    ): string {
        return sprintf(
            'otp:%s_%s',
            $type,
            $target,
        );
    }
}
