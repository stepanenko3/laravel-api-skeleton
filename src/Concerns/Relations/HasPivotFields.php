<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns\Relations;

trait HasPivotFields
{
    protected array $pivotFields = [];

    protected array $pivotRules = [];

    /**
     * Get the pivot fields.
     */
    public function getPivotFields(): array
    {
        return $this->pivotFields;
    }

    /**
     * Set the pivot fields.
     */
    public function withPivotFields(
        array $pivotFields,
    ): self {
        return tap(
            value: $this,
            callback: function () use ($pivotFields): void {
                $this->pivotFields = $pivotFields;
            },
        );
    }

    /**
     * Set the pivot rules.
     */
    public function withPivotRules(
        array $pivotRules
    ): self {
        return tap(
            value: $this,
            callback: function () use ($pivotRules): void {
                $this->pivotRules = $pivotRules;
            }
        );
    }

    /**
     * Get the pivot rules.
     */
    public function getPivotRules(): array
    {
        return $this->pivotRules;
    }
}
