<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Support\{Arr, Collection};

class ExtendedCollection extends Collection
{
    public function groupInto(string $groupKey, string $groupByKey, string $valuesKey)
    {
        $valuesByGroup = $this->groupBy(implode('.', [$groupKey, $groupByKey]));

        return $this
            ->pluck($groupKey)
            ->unique('id')
            ->map(function ($group) use ($valuesByGroup, $groupKey, $groupByKey, $valuesKey) {
                $group[$valuesKey] = (new static($valuesByGroup[$group[$groupByKey]]))
                    ->map(function ($value) use ($groupKey) {
                        unset($value[$groupKey]);

                        return $value;
                    });

                return $group;
            });
    }

    public function dot()
    {
        return new static(Arr::dot($this->toArray()));
    }

    public function whereHas($key, $operator = null, $value = null)
    {
        return $this
            ->filter($this->operatorForWhere(...func_get_args()))
            ->isNotEmpty();
    }

    public function isEmpty(?string $key = null): bool
    {
        if ($key) {
            return empty(
                $this->get(
                    key: $key,
                )
            );
        }

        return empty($this->items);
    }
}
