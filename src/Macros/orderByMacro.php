<?php

use Illuminate\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Traits\HasTranslations;

Builder::macro(
    'orderBy',
    function (
        $field,
        $order = 'asc',
        $locale = null,
    ) {
        if (
            // @phpstan-ignore-next-line
            in_array(HasTranslations::class, class_uses($this->model))
            // @phpstan-ignore-next-line
            && $this->model->isTranslatableAttribute($field)
        ) {
            $locale ??= app()->getLocale();
            $field .= '->' . $locale;
            // @phpstan-ignore-next-line
            $this->query->orderBy($field, $order);
        } else {
            // @phpstan-ignore-next-line
            $this->query->orderBy($field, $order);
        }

        // @phpstan-ignore-next-line
        return $this;
    }
);
