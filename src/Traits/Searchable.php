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

        $sql = [];
        foreach ($this->searchableFields as $field => $params) {
            $weight = $params['weight'] ?? 1;

            $wrapWord = '';

            if (
                in_array(HasTranslations::class, class_uses($this))
                && $this->isTranslatableAttribute($field)
            ) {
                $locale ??= app()->getLocale();
                $field .= '->>\'$.' . $locale . '\'';
                $wrapWord = '';
            }

            if (!($params['strict'] ?? false)) {
                foreach ($words as $word) {
                    $whens = [
                        'WHEN LOWER(' . $field . ') = \'' . $wrapWord . mb_strtolower((string) $word) . $wrapWord . '\' THEN ' . round($weight / count($words) * 2, 2),
                        'WHEN LOWER(' . $field . ') LIKE \'%' . mb_strtolower((string) $word) . '%\' THEN ' . round($weight / count($words), 2),
                    ];

                    $sql[] = '(CASE ' . implode(' ', $whens) . ' ELSE 0 END)';
                }
            }

            $sql[] = '(CASE WHEN LOWER(' . $field . ') = \'' . mb_strtolower(trim($search)) . '\' THEN ' . $weight * 2 . ' ELSE 0 END)';
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
}
