<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Support\Collection;

trait WorkWithUses
{
    public function runMethodOnUses(string $class, string $method, ...$arguments): Collection
    {
        $booted = new Collection;

        foreach (class_uses_recursive($class) as $trait) {
            $classMethod = $method . class_basename($trait);

            if (method_exists($class, $classMethod) && !$booted->has($classMethod)) {
                $result = $this->{$classMethod}(...$arguments);

                $booted->put($classMethod, $result);
            }
        }

        return $booted;
    }
}
