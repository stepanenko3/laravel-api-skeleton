<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use Jaybizzle\CrawlerDetect\CrawlerDetect as Detector;

class CrawlerDetect
{
    private readonly Detector $detector;

    public function __construct(
        ?string $userAgent = null,
    ) {
        $this->detector = new Detector(
            userAgent: $userAgent,
        );
    }

    public function isRobot(): bool
    {
        return $this->detector->isCrawler();
    }

    public function getMatches(): ?string
    {
        return $this->detector->getMatches();
    }
}
