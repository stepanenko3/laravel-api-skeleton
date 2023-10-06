<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

trait Metable
{
    /**
     * The meta array.
     */
    public array $meta = [];

    /**
     * Get the meta data.
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * Set additional meta information for the element.
     *
     * @return $this
     */
    public function withMeta(
        array $meta,
    ): self {
        return tap(
            value: $this,
            callback: function () use ($meta): void {
                $this->meta = array_merge($this->meta, $meta);
            },
        );
    }
}
