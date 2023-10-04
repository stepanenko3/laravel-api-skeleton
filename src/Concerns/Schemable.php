<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns;

use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

trait Schemable
{
    /**
     * The schema.
     */
    public Schema $schema;

    /**
     * Set the schema.
     */
    public function schema(
        Schema $schema,
    ): self {
        return tap(
            value: $this,
            callback: function () use ($schema): void {
                $this->schema = $schema;
            },
        );
    }
}
