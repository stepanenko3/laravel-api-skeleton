<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations\Traits;

use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait Constrained
{
    /**
     * The callback used to determine if the relation is required.
     */
    public mixed $relationRequiredCallback = false;

    /**
     * Set the callback used to determine if the relation is required.
     */
    public function requiredOnCreation(
        mixed $callback = true
    ): self {
        $this->relationRequiredCallback = $callback;

        return $this;
    }

    /**
     * Check required on creation.
     */
    public function isRequiredOnCreation(
        Request $request,
    ): bool {
        if (is_callable($this->relationRequiredCallback)) {
            $this->relationRequiredCallback = call_user_func(
                $this->relationRequiredCallback,
                $request,
                $this->schema(),
            );
        }

        return $this->relationRequiredCallback;
    }
}
