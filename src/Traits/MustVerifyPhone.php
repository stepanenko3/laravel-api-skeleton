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

    abstract public function sendPhoneVerificationOtpCallback(
        OTP $otp,
    ): void;

    public function sendPhoneVerificationOtp(
        ?int $lifetimeSeconds = null,
        ?int $codeLength = null,
    ): void {
        $this
            ->createOTP(
                target: $this->getPhoneForVerification(),
                type: 'phone_verification',
                lifetimeSeconds: $lifetimeSeconds,
                codeLength: $codeLength,
            )
            ->send(fn (OTP $otp) => $this->sendPhoneVerificationOtpCallback(
                otp: $otp,
            ));
    }

    public function usePhoneVerificationOtp(
        string | int $code,
    ): bool {
        if ($this->hasVerifiedPhone()) {
            return true;
        }

        return OTP::use(
            code: $code,
            type: 'phone_verification',
            target: $this->getPhoneForVerification(),
        );
    }

    public function getPhoneForVerification(): string
    {
        return $this->phone;
    }
}
