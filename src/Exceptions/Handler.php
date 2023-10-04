<?php

namespace Stepanenko3\LaravelApiSkeleton\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\BadRequestResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\ErrorResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\NotFoundResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\TooManyAttemptsResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\UnAuthenticatedResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\UnAuthorizedResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\UnprocessableErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = ['password', 'password_confirmation'];

    public function render($request, $e): Responsable
    {
        $this->log(
            exception: $e,
        );

        // if ($request->expectsJson()) {
        if ($e instanceof ThrottleRequestsException) {
            return $this->responseForThrottleRequestsException($e);
        }

        if ($e instanceof ValidationException) {
            return $this->responseForValidationException($e);
        }

        if ($e instanceof ModelNotFoundException) {
            return $this->responseForModelNotFoundException($e);
        }

        if ($e instanceof QueryException) {
            return $this->responseForQueryException($e);
        }

        if ($e instanceof AuthorizationException) {
            return $this->responseForAuthorizationException($e);
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->responseForNotFoundHttpException($e);
        }

        if ($e instanceof UnprocessableEntityHttpException) {
            return $this->responseForUnprocessableEntityHttpException($e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->responseForAuthenticationException($e);
        }

        if ($e instanceof BadRequestHttpException) {
            return $this->responseForBadRequestHttpException($e);
        }

        if ($e instanceof NotAcceptableHttpException) {
            return $this->responseForNotAcceptableHttpException($e);
        }
        // }

        return new ErrorResponse(
            message: $e->getMessage(),
            exception: $e,
        );

        // return parent::render($request, $e);
    }

    protected function log($exception): void
    {
        $logger = $this->container->make(LoggerInterface::class);

        $logger->error(
            $exception->getMessage(),
            array_merge(
                $this->context(),
                [
                    'exception' => $exception,
                ],
            ),
        );
    }

    protected function responseForNotAcceptableHttpException(NotAcceptableHttpException $e): Responsable
    {
        return new ErrorResponse(
            message: 'Not Accessible: ' . $e->getMessage(),
            exception: $e,
            code: Response::HTTP_NOT_ACCEPTABLE,
        );
    }

    protected function responseForBadRequestHttpException(BadRequestHttpException $e): Responsable
    {
        return new BadRequestResponse(
            message: $e->getMessage(),
            exception: $e,
            data: [
                'details' => Str::title(Str::snake(class_basename($e), ' ')),
            ],
        );
    }

    protected function responseForAuthenticationException(AuthenticationException $e): Responsable
    {
        return new UnAuthenticatedResponse(
            message: $e->getMessage(),
            exception: $e,
        );
    }

    protected function responseForUnprocessableEntityHttpException(UnprocessableEntityHttpException $e): Responsable
    {
        return new UnprocessableErrorResponse(
            message: $e->getMessage(),
            exception: $e,
            data: [
                'details' => Str::title(Str::snake(class_basename($e), ' ')),
            ],
        );
    }

    protected function responseForNotFoundHttpException(NotFoundHttpException $e): Responsable
    {
        return new NotFoundResponse(
            message: $e->getMessage(),
            exception: $e,
        );
    }

    protected function responseForAuthorizationException(AuthorizationException $e): Responsable
    {
        return new UnAuthorizedResponse(
            exception: $e,
        );
    }

    protected function responseForQueryException(QueryException $e): Responsable
    {
        if (app()->isProduction()) {
            return new ErrorResponse();
        }

        return new NotFoundResponse(
            message: $e->getMessage(),
            exception: $e,
            data: [
                'details' => Str::title(Str::snake(class_basename($e), ' ')),
            ],
        );
    }

    protected function responseForModelNotFoundException(ModelNotFoundException $e): Responsable
    {
        $id = [] !== $e->getIds() ? ' ' . implode(', ', $e->getIds()) : '.';

        $model = class_basename($e->getModel());

        return new NotFoundResponse(
            message: 'Record not found!',
            exception: $e,
            data: [
                'details' => "{$model} with id {$id} not found",
            ],
        );
    }

    protected function responseForValidationException(ValidationException $e): Responsable
    {
        return new UnprocessableErrorResponse(
            message: $e->getMessage(),
            data: $e->errors(),
            exception: $e,
        );
    }

    protected function responseForThrottleRequestsException(ThrottleRequestsException $e): Responsable
    {
        return new TooManyAttemptsResponse(
            exception: $e,
        );
    }
}
