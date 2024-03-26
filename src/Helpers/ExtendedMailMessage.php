<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Notifications\Messages\MailMessage;

class ExtendedMailMessage extends MailMessage
{
    public array $extendedData = [];

    public function unsubscribe(
        string $url,
    ): self {
        $this->extendedData['unsubscribeUrl'] = $url;

        return $this;
    }

    public function data(): array
    {
        return array_merge(
            parent::data(),
            $this->extendedData,
        );
    }
}
