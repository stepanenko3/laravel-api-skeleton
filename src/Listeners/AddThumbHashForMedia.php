<?php

namespace Stepanenko3\LaravelApiSkeleton\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Stepanenko3\LaravelApiSkeleton\Actions\AddThumbHashForMedia as ActionsAddThumbHashForMedia;

class AddThumbHashForMedia
{
    public function handle(
        MediaHasBeenAddedEvent $event,
    ): void {
        (new ActionsAddThumbHashForMedia())->handle(
            media: $event->media,
        );
    }
}
