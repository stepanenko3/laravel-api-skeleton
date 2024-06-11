<?php

namespace Stepanenko3\LaravelApiSkeleton\Listeners;

use Illuminate\Support\Facades\Storage;
use Thumbhash\Thumbhash;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class AddThumbHashForMedia
{
    public function handle(
        MediaHasBeenAddedEvent $event,
    ): void {
        $content = Storage::disk(config('media-library.disk_name'))->get(get_media_path($event->media));

        $image = (new ImageManager(new Driver()))
            ->read(
                input: $content,
            )
            ->resize(
                width: 100,
                height: 100,
            );

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
