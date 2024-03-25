<?php

namespace Stepanenko3\LaravelApiSkeleton\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TelegramBot
{
    private string $to;

    private array $data;

    private string $bot;

    private array $buttons;

    public function send(
        string $message,
    ) {
        $message = $this->messageMiddleware(
            message: $message,
        );

        return $this->{'sendVia' . Str::ucfirst($this->bot)}(
            $message,
        );
    }

    public function via(
        string $bot,
    ): self {
        $this->bot = $bot;

        return $this;
    }

    public function to(
        string $to,
    ): self {
        $this->to = $to;

        return $this;
    }

    public function data(
        array $data,
    ): self {
        $this->data = $data;

        return $this;
    }

    public function buttons(
        array $buttons,
    ): self {
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

        Http::post(
            url: 'https://api.telegram.org/bot' . config('services.telegram.client_secret') . '/sendMessage',
            data: $data,
        );

        return true;
    }

    private function messageMiddleware(
        string $message,
    ): string {
        return '<a href="' . route('home') . '"><b>' . config('app.name') . '</b></a>'
            . PHP_EOL . PHP_EOL
            . $message;
    }
}
