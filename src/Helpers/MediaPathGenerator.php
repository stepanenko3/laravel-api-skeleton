<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class MediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media) . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media) . '/c/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media) . '/r/';
    }

    // Get a unique base path for the given media.
    private function getBasePath(Media $media, $model = null): string
    {
        $model = $model ?: new $media->model_type();

        if ($model->publicDir) {
            return Storage::disk(config('media-library.disk_name'))
                ->path('/' . $model->getTable() . '/' . $media->model_id . '/' . $media->getKey());
        }

        return Storage::disk('private')
            ->path('/' . $model->getTable() . '/' . $media->model_id . '/' . $media->getKey());
    }
}
