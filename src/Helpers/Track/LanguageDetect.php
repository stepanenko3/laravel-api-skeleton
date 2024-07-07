<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use Illuminate\Http\Request;
use WhichBrowser\Parser;

class LanguageDetect extends Parser
{
    public function detectLanguage(): array
    {
        $languages = $this->getLanguages(
            request: request(),
        );

        return [
            'preference' => $languages[0],
            'language_range' => implode(
                separator: ', ',
                array: $languages,
            ),
        ];
    }

    protected function getLanguages(
        Request $request,
    ): array {
        $acceptLanguage = $request->header(
            key: 'Accept-Language',
        );

        if ($acceptLanguage) {
            $languages = $this->parseAcceptLanguage(
                acceptLanguage: $acceptLanguage,
            );
        }

        return $languages ?? ['en'];
    }

    protected function parseAcceptLanguage(
        string $acceptLanguage,
    ): array {
        $languages = [];

        // Split the header by comma
        $languageRanges = explode(',', $acceptLanguage);

        foreach ($languageRanges as $languageRange) {
            // Split each range by semicolon to separate the language code from the q-value
            $parts = explode(';', $languageRange);

            // The first part is the language code
            $language = trim($parts[0]);

            // Default q-value is 1
            $qValue = 1.0;

            // If there's a q-value specified, parse it
            if (isset($parts[1]) && strpos($parts[1], 'q=') === 0) {
                $qValue = (float) substr($parts[1], 2);
            }

            // Store the language and q-value
            $languages[$language] = $qValue;
        }

        // Sort languages by q-value in descending order
        arsort($languages);

        // Return only the language codes
        return array_keys($languages);
    }
}
