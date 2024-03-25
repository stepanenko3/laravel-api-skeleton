<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\RateLimiter;
use Stepanenko3\LaravelApiSkeleton\Models\Users\UserPhoneToken;

trait MustVerifyPhone
{
    public function getPhone(): ?string
    {
        if ($this->hasVerifiedPhone()) {
            return $this->phone;
        }

        return null;
    }

    public function hasVerifiedPhone(): bool
    {
        return null !== $this->phone_verified_at;
    }

    public function markPhoneAsVerified(): bool
    {
        return $this
            ->forceFill([
                'phone_verified_at' => $this->freshTimestamp(),
            ])
            ->save();
    }

    public function sendPhoneVerificationNotification(): bool
    {
        $token = $this->createPhoneToken();

        try {
            $key = 'user:' . $this->getPhoneForVerification() . ':phone_verification_notification';

            $token->sendCode();

            RateLimiter::increment(
                key: $key,
                decaySeconds: 60,
                amount: 1,
            );

            session([
                'verify_phone_available_at' => RateLimiter::availableIn(
                    key: $key,
                ),
            ]);

            return true;
        } catch (Exception) {
            $token->delete();

            return false;
        }
    }

    public function getPhoneForVerification(): string
    {
        return $this->phone;
    }

    public function createPhoneToken(): UserPhoneToken
    {
        return $this
            ->userPhoneTokens()
            ->create();
    }

    public function userPhoneTokens(): HasMany
    {
        return $this->hasMany(
            related: UserPhoneToken::class,
        );
    }
}
