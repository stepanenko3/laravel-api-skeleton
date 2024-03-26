<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Spatie\Translatable\HasTranslations as SpatieHasTranslations;

trait HasTranslations
{
    use SpatieHasTranslations;

    public function getTranslations(
        ?string $key = null,
    ): array {
        $appLocale = config('app.locale');
        $fallbackLocale = $this->fallbackLocale ?? config('app.fallback_locale');

        if ($key !== null) {
            $this->guardAgainstNonTranslatableAttribute($key);

            $attribute = $this->getAttributes()[$key] ?? '';

            $json = json_decode(
                json: (string) ($attribute ?: '{}'),
                associative: true,
                depth: 512,
                flags: JSON_THROW_ON_ERROR,
            ) ?: [];

            $data = $attribute && (!is_array($json) || count($json) <= 0)
                ? [$fallbackLocale => $attribute]
                : array_filter(
                    $json,
                    fn ($value) => $value !== null && $value !== '',
                );

            if (!isset($data[$appLocale]) && isset($data[$fallbackLocale])) {
                $data[$appLocale] = $data[$fallbackLocale];
            }

            return $data;
        }

        return array_reduce(
            $this->getTranslatableAttributes(),
            function ($result, $item) {
                $result[$item] = $this->getTranslations($item);

                return $result;
            },
        );
    }
}
