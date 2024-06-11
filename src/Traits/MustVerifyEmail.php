<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Stepanenko3\LaravelApiSkeleton\Models\OTP;

trait MustVerifyEmail
{
    use HasOTP;

    public function getEmail(): ?string
    {
        if ($this->hasVerifiedEmail()) {
            return $this->email;
        }

        return null;
    }

    public function hasVerifiedEmail(): bool
    {
        return null !== $this->email_verified_at;
    }

    public function markEmailAsVerified(): bool
    {
        return $this
            ->forceFill([
                'email_verified_at' => now(),
            ])
            ->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        Notification::sendNow(
            notifiables: $this,
            notification: new VerifyEmail(),
        );
    }

    public function useEmailVerificationOtp(
        string | int $code,
    ): bool {
        if ($this->hasVerifiedEmail()) {
            return true;
        }

        return OTP::use(
            code: $code,
            type: 'email_verify',
            target: $this->getEmailForVerification(),
        );
    }

    public function getEmailForVerification(): string
    {
        return $this->email;
    }
}
