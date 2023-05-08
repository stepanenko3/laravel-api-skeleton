<?php

namespace Stepanenko3\LaravelApiSkeleton\Listeners;

use Illuminate\Support\Facades\Storage;
use Thumbhash\Thumbhash;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;
use Intervention\Image\ImageManagerStatic as Image;

class AddThumbHashForMedia
{
    public function handle(MediaHasBeenAdded $event): void
    {
        $content = Storage::disk(config('media-library.disk_name'))->get(get_media_path($event->media));

        $image = Image::make(
            data: $content,
        )->resize(100, 100, function ($constraint): void {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $width = $image->width();
        $height = $image->height();

        $pixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $pixel = $image->pickColor($x, $y);

                $pixels[] = $pixel[0];
                $pixels[] = $pixel[1];
                $pixels[] = $pixel[2];
                $pixels[] = $pixel[3] * 255;
            }
        }

        $hash = Thumbhash::RGBAToHash($width, $height, $pixels);
        $key = Thumbhash::convertHashToString($hash);

        $event->media
            ->setCustomProperty(
                name: 'thumbhash',
                value: $key,
            )
            ->save();
    }
}
