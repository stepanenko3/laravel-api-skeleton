<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use RuntimeException;
use Traversable;

abstract class JsonResource extends BaseJsonResource
{
    public array $attributes = [];

    public array $relationships = [];

    private static $relationshipResourceGuesser;

    public static function guessRelationshipResourceUsing(?callable $callback): void
    {
        self::$relationshipResourceGuesser = $callback;
    }

    public function toAttributes(Request $request): array
    {
        return [];
    }

    public function toRelationships(Request $request): array
    {
        return [];
    }

    public function toMeta(Request $request): array
    {
        return [];
    }

    public function toArray($request): array
    {
        if (null === $this->resource) {
            return [];
        }

        $response = is_array($this->resource)
            ? $this->resource
            : [
                'id' => $this->whenNotNull($this->getKey()),
                'type' => $this->getTable(),
                'attributes' => $this->resolveAttributes($request),
                'meta' => $this->toMeta($request),
                'relations' => $this->resolveRelationships($request),
            ];

        return array_filter($response);
    }

    public function whenLoadMorph(
        string $relationship,
        array $types = [],
        array $map = [],
    ) {
        return $this->whenLoaded(
            relationship: $relationship,
            value: function () use ($relationship, $types, $map) {
                $relation = $this->getRelation($relationship);

                if (!$relation) {
                    return;
                }

                $resource = $types[$relation::class] ?? null;

                if (!$resource) {
                    return;
                }

                $relation = isset($map[$relation::class])
                    ? $map[$relation::class]($relation)
                    : $relation;

                return new $resource($relation);
            },
        );
    }

    public function mapAttributes(array $attributes): array
    {
        return $attributes;
    }

    private static function guessRelationshipResource(string $relationship, self $resource)
    {
        return (self::$relationshipResourceGuesser ?? function (string $relationship, self $resource): string {
            $relationship = Str::of($relationship);

            foreach ([
                "App\\Http\\Resources\\{$relationship->singular()->studly()}Resource",
                "App\\Http\\Resources\\{$relationship->studly()}Resource",
            ] as $class) {
                if (class_exists($class)) {
                    return $class;
                }
            }

            throw new RuntimeException('Unable to guess the resource class for relationship [' . $relationship . '] for [' . $resource::class . '].');
        })($relationship, $resource);
    }

    private function resolveAttributes(Request $request): array
    {
        return Collection::make($this->attributes)
            ->mapWithKeys(function (string $attribute, int | string $key): array {
                $resolvedKey = is_string($key) ? $key : $attribute;

                return [
                    $attribute => $this->whenNotNull(
                        method_exists($this->resource, 'isTranslatableAttribute') && $this->resource->isTranslatableAttribute($resolvedKey)
                            ? $this->resource->getOriginal($resolvedKey)
                            : ($this->resource->{$resolvedKey} ?? $this->resource->getOriginal($resolvedKey)),
                    ),
                ];
            })
            ->merge($this->toAttributes($request))
            ->toArray();
    }

    private function resolveRelationships(Request $request): array
    {
        return Collection::make($this->relationships)
            ->mapWithKeys(fn (string $value, int | string $key) => !is_int($key) ? [
                $key => $value,
            ] : [
                $value => self::guessRelationshipResource($value, $this),
            ])
            ->filter(fn (string $class, string $relation) => $this->resource->relationLoaded($relation))
            ->map(
                fn (string $class, string $relation) => with(
                    $this->resource->{$relation},
                    function (mixed $resource) use ($class) {
                        if ($resource instanceof Traversable || (is_array($resource) && !Arr::isAssoc($resource))) {
                            return $class::collection($resource);
                        }

                        return $class::make($resource);
                    },
                ),
            )
            ->merge($this->toRelationships($request))
            ->toArray();
    }
}
