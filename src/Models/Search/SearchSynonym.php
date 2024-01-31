<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Search;

use Stepanenko3\LaravelApiSkeleton\Traits\Searchable;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

class SearchSynonym extends Model
{
    use Searchable;

    public $searchableFields = [
        'synonyms' => [
            'weight' => 10,
            'strict' => true,
        ],
    ];

    public $timestamps = false;

    protected $fillable = [
        'id',
        'synonyms',
        'right_text',
    ];
}
