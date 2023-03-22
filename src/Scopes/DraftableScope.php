<?php

namespace Stepanenko3\LaravelLogicContainers\Scopes;

use Stepanenko3\LaravelLogicContainers\Traits\Draftable\DraftStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

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

    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getQualifiedDraftStatusColumn(), '=', DraftStatus::PUBLISHED);

        $this->extend($builder);
    }

    public function remove(Builder $builder, Model $model): void
    {
        $builder->withoutGlobalScope($this);

        $column = $model->getQualifiedDraftStatusColumn();
        $query = $builder->getQuery();

        $bindingKey = 0;

        foreach ((array) $query->wheres as $key => $where) {
            if ($this->isDraftableConstraint($where, $column)) {
                $this->removeWhere($query, $key);

                $this->removeBinding($query, $bindingKey);
            }

            if (!in_array($where['type'], ['Null', 'NotNull'])) {
                $bindingKey++;
            }
        }
    }

    public function extend(Builder $builder): void
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }

    private function addWithPublished(Builder $builder): void
    {
        $builder->macro('withPublished', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->where($this->getDraftStatusColumn($builder), DraftStatus::PUBLISHED);
        });
    }

    private function addWithDrafts(Builder $builder): void
    {
        $builder->macro('withDrafts', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder->where($this->getDraftStatusColumn($builder), DraftStatus::DRAFT);
        });
    }

    private function addwithAnyDraftStatus(Builder $builder): void
    {
        $builder->macro('withAnyDraftStatus', function (Builder $builder) {
            $this->remove($builder, $builder->getModel());

            return $builder;
        });
    }

    private function addOnlyPublished(Builder $builder): void
    {
        $builder->macro('onlyPublished', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedDraftStatusColumn(), '=', DraftStatus::PUBLISHED);

            return $builder;
        });
    }

    private function addOnlyDrafts(Builder $builder): void
    {
        $builder->macro('onlyDrafts', function (Builder $builder) {
            $model = $builder->getModel();

            $this->remove($builder, $model);

            $builder->where($model->getQualifiedDraftStatusColumn(), '=', DraftStatus::DRAFT);

            return $builder;
        });
    }

    private function addMarkAsPublished(Builder $builder): void
    {
        $builder->macro('markAsPublished', function (Builder $builder, $id = null) {
            $builder->withAnyDraftStatus();

            return $this->updateDraftableStatus($builder, $id, DraftStatus::PUBLISHED);
        });
    }

    private function addMarkAsDraft(Builder $builder): void
    {
        $builder->macro('markAsDraft', function (Builder $builder, $id = null) {
            $builder->withAnyDraftStatus();

            return $this->updateDraftableStatus($builder, $id, DraftStatus::DRAFT);
        });
    }

    private function getDraftStatusColumn(Builder $builder)
    {
        if ($builder->getQuery()->joins && count($builder->getQuery()->joins) > 0) {
            return $builder->getModel()->getQualifiedDraftStatusColumn();
        }

        return $builder->getModel()->getDraftStatusColumn();
    }

    private function removeWhere($query, $key): void
    {
        unset($query->wheres[$key]);

        $query->wheres = array_values($query->wheres);
    }

    private function removeBinding($query, $key): void
    {
        $bindings = $query->getRawBindings()['where'];

        unset($bindings[$key]);

        $query->setBindings($bindings);
    }

    private function isDraftableConstraint(array $where, $column)
    {
        if (isset($where['column'])) {
            return $where['column'] == $column;
        }

        return false;
    }

    private function updateDraftableStatus(Builder $builder, $id, $status)
    {
        if ($id) {
            $model = $builder->find($id);
            $model->{$model->getDraftStatusColumn()} = $status;
            $model->save();

            return $model;
        }

        return $builder->update([
            $builder->getModel()->getDraftStatusColumn() => $status,
        ]);
    }
}
