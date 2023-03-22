<?php

namespace Stepanenko3\LaravelLogicContainers\Traits;

use Illuminate\Database\Query\Builder as DatabaseBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;

trait Searchable
{
    public function scopeSearch(DatabaseBuilder | EloquentBuilder $query, string $search, array $synonyms = []): void
    {
        $search = str_ireplace(['\'', '"'], ['', ''], $search);
        $words = explode(' ', trim($search));

        // if (!$synonyms && static::class !== Models\SearchSynonym::class) {
        //     $synonyms = Models\SearchSynonym::query()
        //         ->toSearchQuery($search, ['synonyms'])
        //         ->pluck('right_text')
        //         ->toArray();
        // }

        $words = array_unique(array_merge($words, $synonyms));

        $this->bindToSearchQuery($query, $search, $this->searchableFields, $words);
    }

    public function scopeToSearchQuery($query, $search, $fields, $words = null): void
    {
        $this->bindToSearchQuery($query, $search, $fields, $words);
    }

    private function bindToSearchQuery($query, $search, $fields, $words = null): void
    {
        $search = str_ireplace(['\'', '"'], ['', ''], (string) $search);
        $words = $words ?: explode(' ', trim($search));

        $sql = [];
        foreach ($fields as $field) {
            [$key, $weight] = explode(':', $field . ':1');

            $wrapWord = '';

            if (
                in_array(HasTranslations::class, class_uses($this))
                && $this->isTranslatableAttribute($key)
            ) {
                $locale ??= app()->getLocale();
                $key .= '->>\'$.' . $locale . '\'';
                $wrapWord = '';
            }

            foreach ($words as $word) {
                $whens = [
                    'WHEN LOWER(' . $key . ') = \'' . $wrapWord . DB::raw(mb_strtolower((string) $word)) . $wrapWord . '\' THEN ' . round($weight / count($words) * 2, 2),
                    'WHEN LOWER(' . $key . ') LIKE \'%' . DB::raw(mb_strtolower((string) $word)) . '%\' THEN ' . round($weight / count($words), 2),
                ];

                $sql[] = '(CASE ' . DB::raw(implode(' ', $whens)) . ' ELSE 0 END)';
            }

            $sql[] = '(CASE WHEN LOWER(' . $key . ') = \'' . DB::raw(mb_strtolower(trim($search))) . '\' THEN ' . $weight * 2 . ' ELSE 0 END)';
        }

        $calcRelevance = '(' . implode(' + ', $sql) . ')';

        $sql = $calcRelevance . ' AS relevance';

        $query
            ->select('*')
            ->whereRaw(DB::raw($calcRelevance . ' > 0'))
            ->selectRaw($sql)
            ->orderByDesc('relevance');
    }
}
