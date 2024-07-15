<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Stepanenko3\LaravelApiSkeleton\Traits\HasMetaAttributes;

abstract class Model extends BaseModel
{
    use HasMetaAttributes;

    public function newEloquentBuilder($query)
    {
        return new Builder(
            query: $query,
        );
    }

    public function setOnly(
        array $attributes,
    ): self {
        $this->setRawAttributes(
            attributes: $this->only(
                attributes: $attributes,
            ),
            sync: true,
        );

        return $this;
    }
}
