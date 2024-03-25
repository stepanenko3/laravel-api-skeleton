<?php

namespace Stepanenko3\LaravelApiSkeleton\Scopes;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Stepanenko3\LaravelApiSkeleton\Traits\Draftable\DraftStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder;

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
        EloquentBuilder $builder,
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
        Builder $builder,
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
        EloquentBuilder | Builder $builder,
    ): void {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}(
                $builder,
            );
        }
    }

    private function addWithPublished(
        Builder $builder,
    ): void {
        $builder->macro(
            'withPublished',
            function (Builder $builder) {
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
        Builder $builder,
    ): void {
        $builder->macro(
            'withDrafts',
            function (Builder $builder) {
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
        Builder $builder,
    ): void {
        $builder->macro(
            'withAnyDraftStatus',
            function (Builder $builder) {
                $this->remove(
                    builder: $builder,
                    model: $builder->getModel(),
                );

                return $builder;
            }
        );
    }

    private function addOnlyPublished(
        Builder $builder,
    ): void {
        $builder->macro(
            'onlyPublished',
            function (Builder $builder) {
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
        Builder $builder,
    ): void {
        $builder->macro(
            'onlyDrafts',
            function (Builder $builder) {
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
        Builder $builder,
    ): void {
        $builder->macro(
            'markAsPublished',
            function (Builder $builder, $id = null) {
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
        Builder $builder,
    ): void {
        $builder->macro(
            'markAsDraft',
            function (Builder $builder, $id = null) {
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
        Builder $builder,
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
        Builder $query,
        string $key
    ): void {
        unset($query->wheres[$key]);

        $query->wheres = array_values($query->wheres);
    }

    private function removeBinding(
        Builder $query,
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
        Builder $builder,
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
