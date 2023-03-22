<?php

namespace Stepanenko3\LaravelLogicContainers\Traits;

use Exception;

trait MustVerifyPhone
{
    public function getPhone()
    {
        if ($this->hasVerifiedPhone()) {
            return $this->phone;
        }
    }

    /**
     * Determine if the user has verified their phone.
     *
     * @return bool
     */
    public function hasVerifiedPhone()
    {
        return null !== $this->phone_verified_at;
    }

    /**
     * Mark the given user's phone as verified.
     *
     * @return bool
     */
    public function markPhoneAsVerified()
    {
        return $this
            ->forceFill([
                'phone_verified_at' => $this->freshTimestamp(),
            ])
            ->save();
    }

    /**
     * Send the phone verification notification.
     */
    public function sendPhoneVerificationNotification()
    {
        $token = $this->createPhoneToken();

        try {
            $token->sendCode();
            $throttle = throttle_hit();

            session([
                'verify_phone_available_at' => $throttle->availableAt,
            ]);

            return true;
        } catch (Exception $e) {
            $token->delete();

            return false;
        }

        return false;
    }

    /**
     * Get the phone that should be used for verification.
     *
     * @return string
     */
    public function getPhoneForVerification()
    {
        return $this->phone;
    }

    public function createPhoneToken()
    {
        return $this->tokens()->create();
    }
}
