<?php

namespace Stepanenko3\LaravelApiSkeleton\Http\Schemas;

use Laravel\Nova\Actions\Actionable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Authorizable;
use Stepanenko3\LaravelApiSkeleton\Concerns\PerformsModelOperations;
use Stepanenko3\LaravelApiSkeleton\Concerns\PerformsQueries;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemas\ConfiguresRestParameters;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemas\Rulable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemas\Relationable;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;
use Stepanenko3\LaravelApiSkeleton\Instructions\Instructionable;

abstract class Schema
{
    use Actionable;
    use Authorizable;
    use ConfiguresRestParameters;
    use Instructionable;
    use PerformsModelOperations;
    use PerformsQueries;
    use Relationable;
    use Rulable;

    /**
     * Get a fresh instance of the model represented by the resource.
     */
    public static function newModel(): Model
    {
        $model = static::model();

        return new $model();
    }

    abstract public static function model(): string;

    public function fields(
        Request $request,
    ): array {
        return [
            'id',
        ];
    }

    public function defaultFields(
        Request $request,
    ): array {
        return [
            'id',
        ];
    }

    public function relations(
        Request $request,
    ): array {
        return [];
    }

    public function scopes(
        Request $request,
    ): array {
        return [];
    }

    public function limits(
        Request $request,
    ): array {
        return [
            10,
            25,
            50,
        ];
    }

    public function actions(
        Request $request,
    ): array {
        return [];
    }

    public function instructions(
        Request $request,
    ): array {
        return [];
    }

    /**
     * Return the default ordering for resource queries.
     */
    public function defaultOrderBy(
        Request $request,
    ): array {
        return [
            'id' => 'desc',
        ];
    }

    /**
     * Serialize the resource into a JSON-serializable format.
     */
    public function jsonSerialize(): mixed
    {
        $request = app(Request::class);

        return [
            'actions' => collect($this->getActions($request))->map->jsonSerialize()->toArray(),
            'instructions' => collect($this->getInstructions($request))->map->jsonSerialize()->toArray(),
            'fields' => $this->getFields(
                request: $request,
            ),
            'limits' => $this->getLimits(
                request: $request,
            ),
            'scopes' => $this->getScopes(
                request: $request,
            ),
            'relations' => collect(
                value: $this->getRelations(
                    request: $request,
                ),
            )
                ->map
                ->jsonSerialize()
                ->toArray(),

            'rules' => [
                'all' => $this->rules(
                    request: $request,
                ),
                'create' => $this->createRules(
                    request: $request,
                ),
                'update' => $this->updateRules(
                    request: $request,
                ),
            ],
        ];
    }
}
