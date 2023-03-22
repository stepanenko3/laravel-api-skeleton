<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers\Track;

use Snowplow\RefererParser\Parser;

class RefererParser
{
    /**
     * Referer parser instance.
     *
     * @var Parser
     */
    private $parser;

    /**
     * Referer parser instance.
     *
     * @var \Snowplow\RefererParser\Referer
     */
    private $referer;

    /**
     * Create a referer parser instance.
     *
     * @param null|mixed $refererUrl
     * @param null|mixed $pageUrl
     *
     * @return mixed
     */
    public function __construct($refererUrl = null, $pageUrl = null)
    {
        $this->parser = new Parser();
        $this->referer = $this->parser->parse($refererUrl, $pageUrl);
    }

    /**
     * Parse a referer.
     *
     * @param mixed $refererUrl
     * @param mixed $pageUrl
     *
     * @return RefererParser
     */
    public function parse($refererUrl, $pageUrl)
    {
        $this->setReferer($this->parser->parse($refererUrl, $pageUrl));

        return $this;
    }

    /**
     * Get the search medium.
     *
     * @return null|string
     */
    public function getMedium()
    {
        if ($this->isKnown()) {
            return $this->referer->getMedium();
        }
    }

    /**
     * Get the search source.
     *
     * @return null|string
     */
    public function getSource()
    {
        if ($this->isKnown()) {
            return $this->referer->getSource();
        }
    }

    /**
     * Get the search term.
     *
     * @return null|string
     */
    public function getSearchTerm()
    {
        if ($this->isKnown()) {
            return $this->referer->getSearchTerm();
        }
    }

    /**
     * Check if the referer is knwon.
     *
     * @return bool
     */
    public function isKnown()
    {
        return $this->referer->isKnown();
    }

    /**
     * Set the referer.
     *
     * @param \Snowplow\RefererParser\Referer $referer
     *
     * @return RefererParser
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;

        return $this;
    }
}
