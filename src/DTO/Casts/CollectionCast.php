<?php

namespace Stepanenko3\LaravelLogicContainers\DTO\Casts;

use Stepanenko3\LaravelLogicContainers\Interfaces\DtoCastInterface;
use Illuminate\Support\Collection;

class CollectionCast implements DtoCastInterface
{
    public function __construct(
        private ?DtoCastInterface $type = null,
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
