<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Support\Collection;

/** @phpstan-consistent-constructor */
class NestedCollection extends Collection
{
    public function ancestorsOf($id)
    {
        $find = $this->where('id', $id)->first();

        if (!$find) {
            return new static();
        }

        $items = $this
            ->where('id', '!=', $find->id)
            ->where(fn ($item) => $find->_rgt > $item->_lft && $find->_rgt < $item->_rgt);

        return new static($items);
    }

    public function siblingsOf($id)
    {
        $find = $this->where('id', $id)->first();

        if (!$find) {
            return new static();
        }

        $items = $this
            ->where('parent_id', '=', $find->parent_id);

        return new static($items);
    }

    public function descendantsOf($id)
    {
        $find = $this->where('id', $id)->first();

        if (!$find) {
            return new static();
        }

        $items = $this
            ->whereBetween('_lft', [$find->_lft, $find->_rgt]);

        return new static($items);
    }

    public function toTree($root = false)
    {
        if ($this->isEmpty()) {
            return new static();
        }

        $this->linkNodes();

        $items = [];

        $root = $this->getRootNodeId($root);

        /** @var Model|NodeTrait $node */
        foreach ($this->items as $node) {
            if ($node->parent_id == $root) {
                $items[] = $node;
            }
        }

        return new static($items);
    }

    public function linkNodes()
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $groupedNodes = $this->groupBy('parent_id');

        /** @var Model|NodeTrait $node */
        foreach ($this->items as $node) {
            if (!$node->parent_id) {
                $node->parent = null;
            }

            $children = $groupedNodes->get($node->id, []);

            /** @var Model|NodeTrait $child */
            foreach ($children as $child) {
                $child->parent = $node;
            }

            $node->children = Collection::make($children);
        }

        return $this;
    }

    protected function getRootNodeId($root = false)
    {
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
