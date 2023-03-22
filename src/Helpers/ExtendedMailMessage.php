<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Illuminate\Notifications\Messages\MailMessage;

class ExtendedMailMessage extends MailMessage
{
    /**
     * @var string
     */
    public $extendedData = [];

    public function unsubscribe($url)
    {
        $this->extendedData['unsubscribeUrl'] = $url;

        return $this;
    }

    /**
     * Get the data array for the mail message.
     *
     * @return array
     */
    public function data()
    {
        return array_merge(parent::data(), $this->extendedData);
    }
}
