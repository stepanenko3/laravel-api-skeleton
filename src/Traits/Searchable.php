<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;
use Stepanenko3\LaravelApiSkeleton\Models\Search\SearchSynonym;

trait Searchable
{
    public function scopeSearch(
        DatabaseBuilder | EloquentBuilder $query,
        string $search,
        array $synonyms = [],
    ): void {
        $search = str_ireplace(['\'', '"'], ['', ''], $search);
        $words = explode(' ', trim($search));

        if (empty($synonyms) && static::class !== SearchSynonym::class) {
            $synonyms = SearchSynonym::search(
                search: $search,
                synonyms: [
                    'synonyms',
                ],
            )
                ->pluck(
                    column: 'right_text',
                )
                ->toArray();
        }

        $words = array_unique(
            array_merge(
                $words,
                $synonyms,
            ),
        );

        $search = str_ireplace(['\'', '"'], ['', ''], (string) $search);
        $words = $words ?: explode(' ', trim($search));

        $locales = array_keys(
            config('laravellocalization.supportedLocales'),
        );

        $sql = [];

        foreach ($this->searchableFields as $field => $params) {
            if (
                in_array(HasTranslations::class, class_uses($this))
                && $this->isTranslatableAttribute($field)
            ) {
                foreach ($locales as $locale) {
                    $sql = array_merge(
                        $sql,
                        $this->getSearchCases(
                            field: $field . '->>\'$.' . $locale . '\'',
                            search: $search,
                            words: $words,
                            weight: $params['weight'] ?? 1,
                            strict: $params['strict'] ?? false,
                        ),
                    );
                }
            } else {
                $sql = array_merge(
                    $sql,
                    $this->getSearchCases(
                        field: $field,
                        search: $search,
                        words: $words,
                        weight: $params['weight'] ?? 1,
                        strict: $params['strict'] ?? false,
                    ),
                );
            }
        }

        $calcRelevance = '(' . implode(' + ', $sql) . ')';

        $sql = $calcRelevance . ' AS relevance';

        $query
            ->whereRaw(
                sql: DB::raw($calcRelevance . ' > 0'),
            )
            ->selectRaw(
                expression: $sql,
            )
            ->orderByDesc(
                column: 'relevance'
            );
    }

    private function getSearchCases(
        string $field,
        string $search,
        array $words,
        float $weight = 1,
        bool $strict = false,
    ): array {
        $cases = [];

        if (!$strict) {
            foreach ($words as $word) {
                $cases[] = '(CASE WHEN LOWER(' . $field . ') LIKE \'%' . mb_strtolower((string) $word) . '%\' THEN ' . round($weight / count($words), 2) . ' ELSE 0 END)';
            }
        }

        $cases[] = '(CASE WHEN LOWER(' . $field . ') = \'' . mb_strtolower(trim($search)) . '\' THEN ' . $weight * 2 . ' ELSE 0 END)';

        return $cases;
    }
}
