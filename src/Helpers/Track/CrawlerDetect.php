<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers\Track;

use Jaybizzle\CrawlerDetect\CrawlerDetect as Detector;

class CrawlerDetect
{
    /**
     * Crawler detector.
     *
     * @var \Jaybizzle\CrawlerDetect\CrawlerDetect
     */
    private $detector;

    /**
     * Instantiate detector.
     *
     * @param array $headers
     * @param $agent
     * @param null|mixed $userAgent
     */
    public function __construct($userAgent = null)
    {
        $this->detector = new Detector(null, $userAgent);
    }

    /**
     * Check if current request is from a bot.
     *
     * @return bool
     */
    public function isRobot()
    {
        return $this->detector->isCrawler();
    }

    public function getMatches()
    {
        return $this->detector->getMatches();
    }
}
