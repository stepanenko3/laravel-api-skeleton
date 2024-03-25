<?php

namespace Stepanenko3\LaravelApiSkeleton\Channels;

use Illuminate\Notifications\Notification;
use Stepanenko3\LaravelApiSkeleton\Services\TelegramBot;

class TelegramChannel
{
    public function send(
        mixed $notifiable,
        Notification $notification,
    ): void {
        $key = 'telegram';

        $to = $notifiable->routeNotificationFor(
            $key,
            $notification,
        );

        if (!$to) {
            return;
        }

        $data = $notification->{'to' . ucfirst($key)}(
            $notifiable,
        );

        $bot = (new TelegramBot())
            ->via($key)
            ->to($to);

        if (count($data['buttons'] ?? []) > 0) {
            $bot->buttons(
                $data['buttons'],
            );
        }

        $bot->send(
            message: $data['text'],
        );
    }
}
