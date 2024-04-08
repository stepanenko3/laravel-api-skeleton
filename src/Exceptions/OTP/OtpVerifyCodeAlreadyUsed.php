<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions\OTP;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OtpVerifyCodeAlreadyUsed extends Exception
{
    public function __construct()
    {
        parent::__construct(
            message: 'Code already used',
            code: Response::HTTP_BAD_REQUEST,
        );
    }
}
