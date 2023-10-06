<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

trait Schemable
{
    public Schema $schema;

    public function schema(
        Schema $schema,
    ): self {
        $this->schema = $schema;

        return $this;
    }
}
