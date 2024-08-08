<?php

namespace Stepanenko3\LaravelApiSkeleton\Actions;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Gif\Exceptions\NotReadableException;
use Thumbhash\Thumbhash;
use Intervention\Image\ImageManager;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AddThumbHashForMedia
{
    public function handle(
        Media $media,
    ): void {
        // Get the file content
        $content = Storage::disk(config('media-library.disk_name'))
            ->get(
                path: get_media_path(
                    media: $media,
                ),
            );

        try {
            $image = ImageManager::imagick()
                ->read(
                    input: $content,
                )
                ->resize(
                    width: 100,
                    height: 100,
                );

            $image->toJpeg();

            // Get image dimensions
            $width = $image->width();
            $height = $image->height();

            $pixels = [];

            // Extract RGBA values
            for ($y = 0; $y < $height; $y++) {
                for ($x = 0; $x < $width; $x++) {
                    $pixel = $image->pickColor($x, $y);

                    if (!is_array($pixel)) {
                        $pixel = $pixel->toArray();
                    }

                    $pixels[] = $pixel[0];
                    $pixels[] = $pixel[1];
                    $pixels[] = $pixel[2];
                    $pixels[] = $pixel[3];
                }
            }

            // Generate ThumbHash
            $hash = Thumbhash::RGBAToHash($width, $height, $pixels);

            // Convert ThumbHash to string
            $key = Thumbhash::convertHashToString($hash);

            // Set custom property and save the media
            $media->setCustomProperty('thumbhash', $key)->save();
        } catch (NotReadableException $e) {
            // Handle the error
            Log::error('Image could not be decoded: ' . $e->getMessage(), [
                'media_id' => $media->id,
            ]);
            // Optionally, you could set a default value or take some other action
        } catch (Exception $e) {
            // Handle other exceptions
            Log::error('An error occurred: ' . $e->getMessage());
            // Optionally, you could set a default value or take some other action
        } finally {
            // Optionally, you could take some action after the try/catch block
            Log::info("ThumbHash for media {$media->id} has been added");
        }
    }
}
