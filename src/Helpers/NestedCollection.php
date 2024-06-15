<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Support\Collection;

class NestedCollection extends Collection
{
    public function ancestorsOf(
        int | string $id,
    ): self {
        // Use keyBy for faster lookups if not already indexed
        $allItems = $this->keyBy('id');

        $find = $allItems->get($id);

        if (!$find) {
            return new self();
        }

        $items = $this->filter(function ($item) use ($find) {
            return $find->_rgt > $item->_lft && $find->_rgt < $item->_rgt;
        });

        return new self($items->values());
    }

    public function siblingsOf(
        int | string $id,
    ): self {
        $find = $this
            ->where(
                key: 'id',
                operator: '=',
                value: $id,
            )
            ->first();

        if (!$find) {
            return new self();
        }

        $items = $this->where(
            key: 'parent_id',
            operator: '=',
            value: $find->parent_id,
        );

        return new self(
            items: $items,
        );
    }

    public function descendantsOf(
        int | string $id,
    ): self {
        $find = $this
            ->where(
                key: 'id',
                operator: '=',
                value: $id,
            )
            ->first();

        if (!$find) {
            return new self();
        }

        $items = $this->whereBetween(
            key: '_lft',
            values: [
                $find->_lft,
                $find->_rgt,
            ]
        );

        return new self(
            items: $items,
        );
    }

    public function toTree(
        bool | int $root = false,
    ): self {
        if ($this->isEmpty()) {
            return new self();
        }

        $this->linkNodes();

        $items = [];

        $root = $this->getRootNodeId(
            root: $root,
        );

        /** @var Model|NodeTrait $node */
        foreach ($this->items as $node) {
            if ($node->parent_id === $root) {
                $items[] = $node;
            }
        }

        return new self(
            items: $items,
        );
    }

    public function linkNodes(): self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $groupedNodes = $this->groupBy(
            groupBy: 'parent_id',
        );

        /** @var Model|NodeTrait $node */
        foreach ($this->items as $node) {
            if (!$node->parent_id) {
                $node->parent = null;
            }

            $children = $groupedNodes->get(
                key: $node->id,
                default: [],
            );

            /** @var Model|NodeTrait $child */
            foreach ($children as $child) {
                $child->parent = $node;
            }

            $node->children = Collection::make(
                items: $children,
            );
        }

        return $this;
    }

    protected function getRootNodeId(
        bool | int $root = false,
    ): bool | int {
        if ($root !== false) {
            return $root;
        }

        $leastValue = null;

        foreach ($this->items as $node) {
            if ($leastValue === null || $node->_lft < $leastValue) {
                $leastValue = $node->_lft;
                $root = $node->parent_id;
            }
        }

        return $root;
    }
}
