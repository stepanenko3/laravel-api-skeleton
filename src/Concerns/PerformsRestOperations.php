<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\DB;
use Stepanenko3\LaravelApiSkeleton\Contracts\QueryBuilder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\DestroyRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\DetailRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\ForceDestroyRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\MutateRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\OperateRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\RestoreRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\SearchRequest;
use Stepanenko3\LaravelApiSkeleton\Http\Responses\SuccessResponse;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema;

trait PerformsRestOperations
{
    /**
     * Retrieve details of a schema.
     */
    public function details(
        DetailRequest $request,
    ): Responsable {
        $schema = static::schema();

        $schema->authorizeTo(
            ability: 'viewAny',
            model: $schema::newModel(),
        );

        return new SuccessResponse(
            data: $schema->jsonSerialize(),
        );
    }

    /**
     * Search for schemas based on the given criteria.
     */
    public function search(
        SearchRequest $request,
    ): mixed {
        $schema = static::schema();

        $query = app()->make(
            abstract: QueryBuilder::class,
            parameters: [
                'schema' => $schema,
                'query' => null,
            ],
        )->search(
            $request->all(),
        );

        dd($query);

        return $schema::newResponse()
            ->schema($schema)
            ->responsable(
                $schema->paginate($query, $request)
            );
    }

    /**
     * Mutate schemas based on the given request data.
     *
     * @return mixed
     */
    public function mutate(MutateRequest $request)
    {
        $request->schema($schema = static::newSchema());

        DB::beginTransaction();

        $operations = app()->make(QueryBuilder::class, ['schema' => $schema, 'query' => null])
            ->tap(function ($query) use ($request): void {
                self::newSchema()->mutateQuery($request, $query->toBase());
            })
            ->mutate($request->all());

        DB::commit();

        return $operations;
    }

    /**
     * Perform a specific action on the schema.
     *
     * @param string $action
     *
     * @return mixed
     */
    public function operate(OperateRequest $request, $action)
    {
        $request->schema($schema = static::newSchema());

        $actionInstance = $schema->action($request, $action);

        $modelsImpacted = $actionInstance->handleRequest($request);

        return response([
            'data' => [
                'impacted' => $modelsImpacted,
            ],
        ]);
    }

    /**
     * Delete schemas based on the given request.
     *
     * @return mixed
     */
    public function destroy(DestroyRequest $request)
    {
        $request->schema($schema = static::newSchema());

        $query = $schema->destroyQuery($request, $schema::newModel()::query());

        $models = $query
            ->whereIn($schema::newModel()->getKeyName(), $request->input('schemas'))
            ->get();

        foreach ($models as $model) {
            self::newSchema()->authorizeTo('delete', $model);

            $schema->performDelete($request, $model);
        }

        return $schema::newResponse()
            ->schema($schema)
            ->responsable($models);
    }

    /**
     * Restore schemas based on the given request.
     *
     * @return mixed
     */
    public function restore(RestoreRequest $request)
    {
        $request->schema($schema = static::newSchema());

        $query = $schema->restoreQuery($request, $schema::newModel()::query());

        $models = $query
            ->withTrashed()
            ->whereIn($schema::newModel()->getKeyName(), $request->input('schemas'))
            ->get();

        foreach ($models as $model) {
            self::newSchema()->authorizeTo('restore', $model);

            $schema->performRestore($request, $model);
        }

        return $schema::newResponse()
            ->schema($schema)
            ->responsable($models);
    }

    /**
     * Force delete schemas based on the given request.
     *
     * @return mixed
     */
    public function forceDelete(ForceDestroyRequest $request)
    {
        $request->schema($schema = static::newSchema());

        $query = $schema->forceDeleteQuery($request, $schema::newModel()::query());

        $models = $query
            ->withTrashed()
            ->whereIn($schema::newModel()->getKeyName(), $request->input('schemas'))
            ->get();

        foreach ($models as $model) {
            self::newSchema()->authorizeTo('forceDelete', $model);

            $schema->performForceDelete($request, $model);
        }

        return $schema::newResponse()
            ->schema($schema)
            ->responsable($models);
    }

    abstract protected static function schema(): Schema;
}
