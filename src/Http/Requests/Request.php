<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemable;

class Request extends FormRequest
{
    use Schemable;

    public function validationData(): array
    {
        return array_merge(
            $this->route()->parameters(),
            $this->all(),
        );
    }
}
