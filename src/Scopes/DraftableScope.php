<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Stepanenko3\LaravelApiSkeleton\Traits\Draftable\DraftStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilderContract;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Builder;

class DraftableScope implements Scope
{
    private array $extensions = [
        'MarkAsPublished',
        'MarkAsDraft',

        'OnlyPublished',
        'OnlyDrafts',

        'WithPublished',
        'WithDrafts',

        'WithAnyDraftStatus',
    ];

    public function apply(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
        Model $model,
    ): void {
        $builder->where(
            column: $model->getQualifiedDraftStatusColumn(),
            operator: '=',
            value: DraftStatus::PUBLISHED,
        );

        $this->extend(
            builder: $builder,
        );
    }

    public function remove(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
        Model $model,
    ): void {
        $builder->withoutGlobalScope(
            scope: $this,
        );

        $column = $model->getQualifiedDraftStatusColumn();
        $query = $builder->getQuery();

        $bindingKey = 0;

        foreach ((array) $query->wheres as $key => $where) {
            if ($this->isDraftableConstraint(
                where: $where,
                column: $column,
            )) {
                $this->removeWhere(
                    query: $query,
                    key: $key,
                );

                $this->removeBinding(
                    query: $query,
                    key: $bindingKey,
                );
            }

            if (!in_array($where['type'], ['Null', 'NotNull'])) {
                $bindingKey++;
            }
        }
    }

    public function extend(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}(
                $builder,
            );
        }
    }

    private function addWithPublished(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'withPublished',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder) {
                $this->remove(
                    builder: $builder,
                    model: $builder->getModel(),
                );

                return $builder->where(
                    column: $this->getDraftStatusColumn(
                        builder: $builder,
                    ),
                    operator: '=',
                    value: DraftStatus::PUBLISHED,
                );
            }
        );
    }

    private function addWithDrafts(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'withDrafts',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder) {
                $this->remove(
                    builder: $builder,
                    model: $builder->getModel(),
                );

                return $builder->where(
                    column: $this->getDraftStatusColumn($builder),
                    operator: '=',
                    value: DraftStatus::DRAFT,
                );
            }
        );
    }

    private function addwithAnyDraftStatus(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'withAnyDraftStatus',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder) {
                $this->remove(
                    builder: $builder,
                    model: $builder->getModel(),
                );

                return $builder;
            }
        );
    }

    private function addOnlyPublished(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'onlyPublished',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder) {
                $model = $builder->getModel();

                $this->remove(
                    builder: $builder,
                    model: $model,
                );

                $builder->where(
                    column: $model->getQualifiedDraftStatusColumn(),
                    operator: '=',
                    value: DraftStatus::PUBLISHED,
                );

                return $builder;
            }
        );
    }

    private function addOnlyDrafts(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'onlyDrafts',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder) {
                $model = $builder->getModel();

                $this->remove(
                    builder: $builder,
                    model: $model,
                );

                $builder->where(
                    column: $model->getQualifiedDraftStatusColumn(),
                    operator: '=',
                    value: DraftStatus::DRAFT,
                );

                return $builder;
            }
        );
    }

    private function addMarkAsPublished(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'markAsPublished',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder, $id = null) {
                $builder->withAnyDraftStatus();

                return $this->updateDraftableStatus(
                    builder: $builder,
                    id: $id,
                    status: DraftStatus::PUBLISHED,
                );
            }
        );
    }

    private function addMarkAsDraft(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): void {
        $builder->macro(
            'markAsDraft',
            function (EloquentBuilderContract | QueryBuilderContract | Builder $builder, $id = null) {
                $builder->withAnyDraftStatus();

                return $this->updateDraftableStatus(
                    builder: $builder,
                    id: $id,
                    status: DraftStatus::DRAFT,
                );
            }
        );
    }

    private function getDraftStatusColumn(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
    ): string {
        if ($builder->getQuery()->joins && count($builder->getQuery()->joins) > 0) {
            return $builder
                ->getModel()
                ->getQualifiedDraftStatusColumn();
        }

        return $builder
            ->getModel()
            ->getDraftStatusColumn();
    }

    private function removeWhere(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
        string $key
    ): void {
        unset($query->wheres[$key]);

        $query->wheres = array_values($query->wheres);
    }

    private function removeBinding(
        EloquentBuilderContract | QueryBuilderContract | Builder $query,
        string $key,
    ): void {
        $bindings = $query->getRawBindings()['where'];

        unset($bindings[$key]);

        $query->setBindings(
            bindings: $bindings,
        );
    }

    private function isDraftableConstraint(
        array $where,
        string $column,
    ): bool {
        if (isset($where['column'])) {
            return $where['column'] == $column;
        }

        return false;
    }

    private function updateDraftableStatus(
        EloquentBuilderContract | QueryBuilderContract | Builder $builder,
        $id,
        $status
    ): int {
        if ($id) {
            $model = $builder->find(
                id: $id,
            );

            $model->{$model->getDraftStatusColumn()} = $status;
            $model->save();

            return $id;
        }

        return $builder->update(
            values: [
                $builder->getModel()->getDraftStatusColumn() => $status,
            ],
        );
    }
}
