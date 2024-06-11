<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stepanenko3\LaravelApiSkeleton\Models\OTP;

trait HasOTP
{
    public function sentOtpCallback(
        OTP $otp,
    ): void {
        //
    }

    public function otp(): MorphMany
    {
        return $this
            ->morphMany(
                related: OTP::class,
                name: 'for',
            );
    }

    public function createOTP(
        string $target,
        string $type,
        ?int $lifetimeSeconds = null,
        ?int $codeLength = null,
    ): OTP {
        return $this
            ->otp()
            ->create(
                attributes: [
                    'code' => OTP::generateCode(
                        codeLength: $codeLength,
                    ),
                    'used' => 0,
                    'target' => $target,
                    'type' => $type,
                    'expires_at' => now()->addSeconds(
                        $lifetimeSeconds ?? config('api-skeleton.otp.lifetime'),
                    ),
                ],
            );
    }

    public function sentOtp(
        string $target,
        string $type,
        ?int $lifetimeSeconds = null,
        ?int $codeLength = null,
    ): void {
        $this
            ->otp()
            ->where(
                column: 'type',
                operator: '=',
                value: $type,
            )
            ->delete();

        $otp = $this->createOTP(
            target: $target,
            type: $type,
            lifetimeSeconds: $lifetimeSeconds,
            codeLength: $codeLength,
        );

        $otp->send(
            callback: fn (OTP $otp) => $this->sentOtpCallback(
                otp: $otp,
            ),
        );
    }
}
