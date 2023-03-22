<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers\Track;

use Jenssegers\Agent\Agent;

class LanguageDetect extends Agent
{
    public function __construct($userAgent = null)
    {
        parent::__construct();

        if ($userAgent) {
            $this->setUserAgent($userAgent);
        }
    }

    /**
     * Detect preference and language-range.
     *
     * @return array
     */
    public function detectLanguage()
    {
        return [
            'preference' => $this->getLanguagePreference(),
            'language_range' => $this->getLanguageRange(),
        ];
    }

    /**
     * Get language prefernece.
     *
     * @return string
     */
    public function getLanguagePreference()
    {
        $languages = $this->languages();

        return count($languages) ? $languages[0] : 'en';
    }

    /**
     * Get languages ranges.
     *
     * @return string
     */
    public function getLanguageRange()
    {
        return implode(',', $this->languages());
    }
}
