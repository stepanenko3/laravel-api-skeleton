<?php

namespace Stepanenko3\LaravelApiSkeleton\Http;

use Illuminate\Foundation\Http\FormRequest;

class Request extends FormRequest
{
    public function validationData(): array
    {
        return array_merge(
            $this->route()->parameters(),
            $this->all(),
        );
    }
}
