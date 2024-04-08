<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions\OTP;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class OtpVerifyCodeExpired extends Exception
{
    public function __construct()
    {
        parent::__construct(
            message: 'Code expired',
            code: Response::HTTP_BAD_REQUEST,
        );
    }
}
