<?php

namespace Stepanenko3\LaravelApiSkeleton\Models\Seo;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Traits\HasTranslations;

class SeoMeta extends Model
{
    use HasTranslations;

    protected $table = 'seo_metadata';

    protected $translatable = [
        'title',
        'meta_title',
        'meta_description',
        'body',
        'faq',
    ];

    protected $fillable = [
        'for_id',
        'for_type',
        'title',
        'meta_title',
        'meta_description',
        'body',
        'faq',
        'status',
    ];

    public function for(): MorphTo
    {
        return $this->morphTo(
            name: 'for',
        );
    }
}
