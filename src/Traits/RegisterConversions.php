<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait RegisterConversions
{
    use InteractsWithMedia;

    protected ?array $conversions = null;

    protected array $conversionsExtensions = [];

    public function media(): MorphMany
    {
        return $this
            ->morphMany(
                related: config('media-library.media_model'),
                name: 'model',
            )
            ->select([
                'id',
                'model_type',
                'model_id',
                'collection_name',
                'mime_type',
                'file_name',
                'disk',
                'conversions_disk',
                'custom_properties',
                'generated_conversions',
                'size',
                'order_column',
                'created_at',
                'updated_at',
            ]);
    }

    public function latestMedia(): MorphOne
    {
        return $this
            ->morphOne(
                related: config('media-library.media_model'),
                name: 'model',
            )
            ->latest();
    }

    public function oldestMedia(): MorphOne
    {
        return $this
            ->morphOne(
                related: config('media-library.media_model'),
                name: 'model',
            )
            ->oldest();
    }

    public function setCoversions($conversions): void
    {
        $this->conversions = $conversions;
    }

    public function getConversionExtension($conversion)
    {
        if (!$this->conversionsExtensions) {
            $this->loadConversions();
        }

        return $this->conversionsExtensions[$conversion];
    }

    public function getConversionExtensions()
    {
        return $this->conversionsExtensions;
    }

    public function getConversions()
    {
        if (!$this->conversions) {
            $this->loadConversions();
        }

        return $this->conversions;
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $conversions = $this->getConversions();

        foreach ($conversions as $key => $dimemsions) {
            $this->addMediaConversion($key)
                // ->watermark(public_path('assets/img/logo-white.png'))
                // ->watermarkPosition(Manipulations::POSITION_BOTTOM_RIGHT)
                // ->watermarkHeight(25, Manipulations::UNIT_PERCENT)
                // ->watermarkWidth(25, Manipulations::UNIT_PERCENT)
                // ->watermarkPadding(12, 12, Manipulations::UNIT_PERCENT)
                // ->watermarkOpacity(50)
                ->width($dimemsions['width'])
                ->height($dimemsions['height'])
                ->format('png')
                ->optimize()
                ->performOnCollections('*')
                ->nonQueued();

            $this->addMediaConversion('webp-' . $key)
                // ->watermark(public_path('assets/img/logo-white.png'))
                // ->watermarkPosition(Manipulations::POSITION_BOTTOM_RIGHT)
                // ->watermarkHeight(25, Manipulations::UNIT_PERCENT)
                // ->watermarkWidth(25, Manipulations::UNIT_PERCENT)
                // ->watermarkPadding(12, 12, Manipulations::UNIT_PERCENT)
                // ->watermarkOpacity(50)
                ->width($dimemsions['width'])
                ->height($dimemsions['height'])
                ->format('webp')
                ->optimize()
                ->performOnCollections('*')
                ->nonQueued();
        }
    }

    protected function loadConversions(): void
    {
        $this->conversions = array_merge(
            $this->modelMediaConversions ?? [],
            $this->getMediaConversions(),
        );

        $conversions = array_keys($this->conversions);

        foreach ($conversions as $key) {
            $this->conversionsExtensions[$key] = 'png';
            $this->conversionsExtensions['webp-' . $key] = 'webp';
        }
    }

    abstract protected function getMediaConversions(): array;
}
