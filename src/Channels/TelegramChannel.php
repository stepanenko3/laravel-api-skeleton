<?php

namespace Stepanenko3\LaravelLogicContainers\Channels;

use Illuminate\Notifications\Notification;
use Stepanenko3\LaravelLogicContainers\Services\TelegramBot;

class TelegramChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     */
    public function send($notifiable, Notification $notification): void
    {
        $key = 'telegram';

        $to = $notifiable->routeNotificationFor($key, $notification);
        if (!$to) {
            return;
        }

        $data = $notification->{'to' . ucfirst($key)}($notifiable);

        $bot = (new TelegramBot())
            ->via($key)
            ->to($to);

        if (count($data['buttons'] ?? []) > 0) {
            $bot->buttons($data['buttons']);
        }

        $bot->send($data['text']);
    }
}
