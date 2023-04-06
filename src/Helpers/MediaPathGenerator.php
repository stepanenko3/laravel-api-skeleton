<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

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

    private function getBasePath(Media $media, $model = null): string
    {
        $model = $model ?: new $media->model_type();

        return '/' . $model->getTable() . '/' . $media->model_id . '/' . $media->getKey();
    }
}
