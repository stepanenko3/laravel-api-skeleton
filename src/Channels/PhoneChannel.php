<?php

namespace Stepanenko3\LaravelApiSkeleton\Channels;

use Stepanenko3\LaravelApiSkeleton\Services\SMS;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PhoneChannel
{
    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        $key = 'phone';

        $to = $notifiable->routeNotificationFor($key, $notification);
        if (!$to) {
            return;
        }

        $message = $notification->{'to' . ucfirst($key)}($notifiable);

        $status = (new SMS())
            ->send($to, $message);

        if ($status) {
            return true;
        }

        Log::warning("SMS '{$message}' to {$to} was not sent");

        return false;
    }
}
