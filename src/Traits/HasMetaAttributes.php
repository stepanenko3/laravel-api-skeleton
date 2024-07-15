<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

trait HasMetaAttributes
{
    protected array $metaAttributes = [];

    public function getMetaAttributes(): array
    {
        return $this->metaAttributes;
    }

    public function getMetaAttribute(
        string $key,
        mixed $default = null,
    ): mixed {
        return data_get(
            target: $this->metaAttributes,
            key: $key,
            default: $default,
        );
    }

    public function setMetaAttribute(
        string $key,
        mixed $value,
    ): self {
        $this->metaAttributes[$key] = $value;
        // data_set(
        //     target: $this->metaAttributes,
        //     key: $key,
        //     value: $value,
        // );

        return $this;
    }

    public function fillMetaAttributes(
        array $attributes,
    ): self {
        $this->metaAttributes = array_merge(
            $this->metaAttributes,
            $attributes,
        );

        return $this;
    }

    public function fillForceMetaAttributes(
        array $attributes,
    ): self {
        $this->metaAttributes = $attributes;

        return $this;
    }
}
