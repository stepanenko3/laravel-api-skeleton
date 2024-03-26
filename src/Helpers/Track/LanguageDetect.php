<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use Jenssegers\Agent\Agent;

class LanguageDetect extends Agent
{
    public function __construct(
        ?string $userAgent = null,
    ) {
        parent::__construct();

        if ($userAgent) {
            $this->setUserAgent($userAgent);
        }
    }

    public function detectLanguage(): array
    {
        return [
            'preference' => $this->getLanguagePreference(),
            'language_range' => $this->getLanguageRange(),
        ];
    }

    public function getLanguagePreference(): string
    {
        $languages = $this->languages();

        return count($languages)
            ? $languages[0]
            : 'en';
    }

    public function getLanguageRange(): string
    {
        return implode(',', $this->languages());
    }
}
