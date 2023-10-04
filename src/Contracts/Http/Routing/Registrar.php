<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts\Http\Routing;

use Stepanenko3\LaravelApiSkeleton\Http\Controllers\Controller;

interface Registrar
{
    /**
     * Route a resource to a controller.
     *
     * @return Stepanenko3\LaravelApiSkeleton\Http\Routing\PendingResourceRegistration
     */
    public function resource(string $name, string $controller, array $options = []);
}
