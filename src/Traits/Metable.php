<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

trait Metable
{
    public array $meta = [];

    public function meta(): array
    {
        return $this->meta;
    }

    public function withMeta(
        array $meta,
    ): self {
        return tap(
            value: $this,
            callback: function () use ($meta): void {
                $this->meta = array_merge(
                    $this->meta,
                    $meta,
                );
            },
        );
    }
}
