<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use UAParser\Parser;

class UserAgentParser
{
    public $parser;

    public $ua;

    public $os;

    public $device;

    public $originalUserAgent;

    public function __construct($userAgent = null)
    {
        if (!$userAgent) {
            $userAgent = request()->userAgent();
        }

        $this->parser = Parser::create()->parse($userAgent);

        $this->ua = $this->parser->ua;
        $this->os = $this->parser->os;
        $this->device = $this->parser->device;
        $this->originalUserAgent = $this->parser->originalUserAgent;
    }

    public function getOSVersion()
    {
        return $this->os->major
            . ($this->os->minor !== null ? '.' . $this->os->minor : '')
            . ($this->os->patch !== null ? '.' . $this->os->patch : '');
    }

    public function getUAVersion()
    {
        return $this->ua->major
            . ($this->ua->minor !== null ? '.' . $this->ua->minor : '')
            . ($this->ua->patch !== null ? '.' . $this->ua->patch : '');
    }
}
