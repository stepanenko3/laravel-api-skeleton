<?php

namespace Stepanenko3\LaravelApiSkeleton\Relations;

use Closure;
use Illuminate\Support\Str;
use JsonSerializable;
use ReflectionClass;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Relations\HasPivotFields;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;
use Stepanenko3\LaravelApiSkeleton\Relations\Traits\Constrained;
use Stepanenko3\LaravelApiSkeleton\Relations\Traits\Mutates;
use Stepanenko3\LaravelApiSkeleton\Rules\RequiredRelationOnCreation;

class Relation implements JsonSerializable
{
    use Constrained;
    use HasPivotFields;
    use Makeable;
    use Mutates;

    public string $relation;

    /**
     * The displayable name of the relation.
     */
    public string $name;

    protected array $types;

    protected Schema $fromSchema;

    public function __construct(
        string $relation,
        string $type,
    ) {
        $this->relation = $relation;
        $this->types = [$type];
    }

    /**
     * Get the name of the relation.
     */
    public function name(): string
    {
        return $this->name ?: (new ReflectionClass($this))->getShortName();
    }

    /**
     * Filter the query based on the relation.
     */
    public function filter(
        Builder | EloquentBuilder $query,
        mixed $relation,
        mixed $operator,
        mixed $value,
        string $boolean = 'and',
        Closure | null $callback = null,
    ): Builder | EloquentBuilder {
        return $query->has(
            relation: Str::beforeLast(
                subject: relation_without_pivot($relation),
                search: '.',
            ),
            operator: '>=',
            count: 1,
            boolean: $boolean,
            callback: function (Builder | EloquentBuilder $query) use ($value, $operator, $relation, $callback): void {
                if (Str::contains(
                    haystack: $relation,
                    needles: '.pivot.'
                )) {
                    $relationMethod = Str::of(
                        string: $relation,
                    )
                        ->before(
                            search: '.pivot.',
                        )
                        ->afterLast(
                            search: '.',
                        )
                        ->toString();

                    $prefix = $this->fromSchema::newModel()
                        ->{$relationMethod}()
                        ->getTable();
                } else {
                    $prefix = $query->getModel()->getTable();
                }

                $field = $prefix . '.' . Str::afterLast($relation, '.');

                if (in_array($operator, ['in', 'not in'])) {
                    $query->whereIn($field, $value, 'and', $operator === 'not in');
                } else {
                    $query->where(
                        column: $field,
                        operator: $operator,
                        value: $value,
                    );
                }

                $callback($query);
            }
        );
    }

    /**
     * Apply a search query to the relation's builder.
     */
    public function applySearchQuery(Builder $query): void
    {
        $schema = $this->schema();

        $schema->searchQuery(
            request: app()->make(
                abstract: Request::class,
            ),
            query: $query,
        );
    }

    /**
     * Check if the relation has multiple entries.
     */
    public function hasMultipleEntries(): bool
    {
        return false;
    }

    /**
     * Get the schema associated with this relation.
     */
    public function schema(): Schema
    {
        return new $this->types[0]();
    }

    /**
     * Set the "fromSchema" property of the relation.
     */
    public function fromSchema(Schema $fromSchema): self
    {
        return tap(
            value: $this,
            callback: function () use ($fromSchema): void {
                $this->fromSchema = $fromSchema;
            },
        );
    }

    /**
     * Get the validation rules for this relation.
     */
    public function rules(
        Schema $schema,
        string $prefix,
    ): array {
        $rules = [];

        if ($this->isRequiredOnCreation(
            request: app()->make(
                abstract: Request::class,
            )
        )) {
            $rules[$prefix] = [
                RequiredRelationOnCreation::make()
                    ->schema(
                        schema: $schema
                    ),
            ];
        }

        // if (in_array(
        //     needle: HasPivotFields::class,
        //     haystack: class_uses_recursive($this),
        //     strict: true,
        // )) {
        $pivotPrefix = $prefix;

        if ($this->hasMultipleEntries()) {
            $pivotPrefix .= '.*';
        }

        $pivotPrefix .= '.pivot.';

        foreach ($this->getPivotRules() as $pivotKey => $pivotRule) {
            $rules[$pivotPrefix . $pivotKey] = $pivotRule;
        }
        // }

        return $rules;
    }

    /**
     * Serialize the object to JSON.
     */
    public function jsonSerialize(): array
    {
        $request = app(Request::class);

        return [
            'schemas' => $this->types,
            'relation' => $this->relation,
            'constraints' => [
                'requiredOnCreation' => $this->isRequiredOnCreation(
                    request: $request,
                ),
            ],
            'name' => $this->name(),
        ];
    }
}
