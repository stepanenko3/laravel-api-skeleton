<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use UAParser\Parser;
use UAParser\Result\Client;
use UAParser\Result\Device;
use UAParser\Result\OperatingSystem;
use UAParser\Result\UserAgent;

class UserAgentParser
{
    public Client $parser;

    public UserAgent $ua;

    public OperatingSystem $os;

    public Device $device;

    public string $originalUserAgent;

    public function __construct(
        ?string $userAgent = null,
    ) {
        if (!$userAgent) {
            $userAgent = request()->userAgent();
        }

        $this->parser = Parser::create()
            ->parse(
                userAgent: $userAgent,
            );

        $this->ua = $this->parser->ua;
        $this->os = $this->parser->os;
        $this->device = $this->parser->device;
        $this->originalUserAgent = $this->parser->originalUserAgent;
    }

    public function getOSVersion(): string
    {
        return $this->os->major
            . ($this->os->minor !== null ? '.' . $this->os->minor : '')
            . ($this->os->patch !== null ? '.' . $this->os->patch : '');
    }

    public function getUAVersion(): string
    {
        return $this->ua->major
            . ($this->ua->minor !== null ? '.' . $this->ua->minor : '')
            . ($this->ua->patch !== null ? '.' . $this->ua->patch : '');
    }
}
