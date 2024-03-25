<?php

namespace Stepanenko3\LaravelApiSkeleton\Services;

use Exception;
use Illuminate\Support\Facades\{Http, Log};

class SMS
{
    private string $via = 'smsclub';

    public function send(string $phone, string $message): self
    {
        return ($this->{$this->via})($phone, $message);
    }

    public function via(string $via): self
    {
        if (!in_array($via, ['smsclub, twilio'])) {
            throw new Exception('undefined provider ' . $via);
        }

        $this->via = $via;

        return $this;
    }

    protected function smsclub(string $phone, string $message): bool
    {
        $res = Http::withToken(env('SMSCLUB_TOKEN'))
            ->post('https://im.smsclub.mobi/sms/send', [
                'src_addr' => 'Shop Zakaz',
                'phone' => [$phone],
                'message' => $message,
            ]);

        $json = $res->json();

        if (count($json['success_request']['info'] ?? []) > 0) {
            return true;
        }

        return false;
    }

    // protected function twilio(string $phone, string $message): bool
    // {
    //     try {
    //         $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));

    //         $client->messages->create($phone, [
    //             'from' => env('TWILIO_NUMBER'),
    //             'body' => $message,
    //         ]);
    //     } catch (Exception $e) {
    //         Log::error($e->getMessage());

    //         return false;
    //     }

    //     return true;
    // }
}
