<?php

namespace Stepanenko3\LaravelApiSkeleton\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelegramBot
{
    private $to;

    private $data;

    private $bot;

    private $buttons;

    public function send($message)
    {
        $message = $this->messageMiddleware($message);

        return $this->{'sendVia' . Str::ucfirst($this->bot)}($message);
    }

    public function via($bot)
    {
        $this->bot = $bot;

        return $this;
    }

    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function buttons($buttons)
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function sendViaTelegram($message)
    {
        $data = [
            'chat_id' => $this->to,
            'parse_mode' => 'html',
            'text' => $message,
            'disable_web_page_preview' => true,
            ...($this->data ?: []),
        ];

        if ($this->buttons) {
            $buttons = [];
            foreach ($this->buttons as $button) {
                $buttons[] = [
                    'text' => $button['text'],
                    'url' => $button['url'],
                ];
            }

            $data['reply_markup'] = [
                'inline_keyboard' => [
                    $buttons,
                ],
            ];
        }

        Http::post('https://api.telegram.org/bot' . config('services.telegram.client_secret') . '/sendMessage', $data);

        return true;
    }

    private function messageMiddleware($message)
    {
        return '<a href="' . route('home') . '"><b>' . config('app.name') . '</b></a>'
            . PHP_EOL . PHP_EOL
            . $message;
    }
}
