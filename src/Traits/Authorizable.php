<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

trait Authorizable
{
    /**
     * Check if authorizations are enabled.
     */
    public function isAuthorizingEnabled(): bool
    {
        return true;
    }

    /**
     * Determine if the current user has a given ability.
     */
    public function authorizeTo(
        string $ability,
        Model $model,
    ): void {
        if ($this->isAuthorizingEnabled()) {
            $gatePasses = Gate::authorize(
                ability: $ability,
                arguments: $model,
            );

            if (!$gatePasses) {
                Response::deny()->authorize();
            }
        }
    }

    /**
     * Determine if the current user can view the given resource.
     */
    public function authorizedTo(
        string $ability,
        Model $model,
    ): bool {
        if ($this->isAuthorizingEnabled()) {
            return Gate::check(
                abilities: $ability,
                arguments: $model,
            );
        }

        return true;
    }
}
