<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use Snowplow\RefererParser\Parser;
use Snowplow\RefererParser\Referer;

class RefererParser
{
    private Parser $parser;
    private Referer $referer;

    public function __construct(
        ?string $refererUrl = null,
        ?string $pageUrl = null,
    ) {
        $this->parser = new Parser();
        $this->referer = $this->parser->parse(
            refererUrl: $refererUrl,
            pageUrl: $pageUrl,
        );
    }

    public function parse(
        mixed $refererUrl,
        mixed $pageUrl,
    ): self {
        $this->setReferer(
            referer: $this->parser->parse(
                refererUrl: $refererUrl,
                pageUrl: $pageUrl,
            )
        );

        return $this;
    }

    public function getMedium(): ?string
    {
        if ($this->isKnown()) {
            return $this->referer->getMedium();
        }

        return null;
    }

    public function getSource(): ?string
    {
        if ($this->isKnown()) {
            return $this->referer->getSource();
        }

        return null;
    }

    public function getSearchTerm(): ?string
    {
        if ($this->isKnown()) {
            return $this->referer->getSearchTerm();
        }

        return null;
    }

    public function isKnown(): bool
    {
        return $this->referer->isKnown();
    }

    public function setReferer(
        Referer $referer,
    ): self {
        $this->referer = $referer;

        return $this;
    }
}
