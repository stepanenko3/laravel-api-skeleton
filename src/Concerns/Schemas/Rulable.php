<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns\Schemas;

use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait Rulable
{
    /**
     * Get the validation rules for resource requests.
     */
    public function rules(
        Request $request,
    ): array {
        return [];
    }

    /**
     * Get the validation rules for resource creation requests.
     */
    public function createRules(
        Request $request
    ): array {
        return [];
    }

    /**
     * Get the validation rules for resource update requests.
     */
    public function updateRules(
        Request $request
    ): array {
        return [];
    }
}
