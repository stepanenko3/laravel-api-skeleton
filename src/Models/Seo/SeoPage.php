<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Seo;

use Illuminate\Database\Eloquent\SoftDeletes;
use Stepanenko3\LaravelApiSkeleton\Traits\Draftable\Draftable;
use Stepanenko3\LaravelApiSkeleton\Traits\HasTranslations;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;

final class SeoPage extends Model
{
    use Draftable;
    use HasTranslations;
    use SoftDeletes;

    protected $translatable = [
        'title',
        'meta_title',
        'meta_description',
        'body',
        'faq',
    ];

    protected $fillable = [
        'title',
        'meta_title',
        'meta_description',
        'body',
        'faq',
        'status',
    ];
}
