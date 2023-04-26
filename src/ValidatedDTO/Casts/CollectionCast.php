<?php

namespace Stepanenko3\LaravelApiSkeleton\ValidatedDTO\Casts;

use Stepanenko3\LaravelApiSkeleton\Contracts\ValidatedDtoCastContract;
use Illuminate\Support\Collection;

class CollectionCast implements ValidatedDtoCastContract
{
    public function __construct(
        private readonly ?ValidatedDtoCastContract $type = null,
    ) {
        //
    }

    public function cast(
        string $property,
        mixed $value,
    ): Collection {
        $arrayCast = new ArrayCast();
        $value = $arrayCast->cast($property, $value);

        return Collection::make($value)
            ->when(
                $this->type,
                fn ($collection, $castable) => $collection->map(fn ($item) => $castable->cast($property, $item)),
            );
    }
}
