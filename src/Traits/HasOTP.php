<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Stepanenko3\LaravelApiSkeleton\Models\OTP;

trait HasOTP
{
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
        return OTP::query()->create(
            attributes: [
                'code' => OTP::generateCode(
                    codeLength: $codeLength,
                ),
                'used' => 0,
                'for_type' => get_class($this),
                'for_id' => $this->getKey(),
                'target' => $target,
                'type' => $type,
                'expires_at' => now()->addSeconds(
                    $lifetimeSeconds ?? config('api-skeleton.otp.lifetime'),
                ),
            ],
        );
    }
}
