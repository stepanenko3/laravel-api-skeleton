<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Stepanenko3\LaravelApiSkeleton\Models\OTP;

trait MustVerifyPhone
{
    use HasOTP;

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
                'phone_verified_at' => now(),
            ])
            ->save();
    }

    public function usePhoneVerificationOtp(
        string | int $code,
    ): bool {
        if ($this->hasVerifiedPhone()) {
            return true;
        }

        return OTP::use(
            code: $code,
            type: 'phone_verify',
            target: $this->getPhoneForVerification(),
        );
    }

    public function getPhoneForVerification(): string
    {
        return $this->phone;
    }
}
