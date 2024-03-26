<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use Jenssegers\Agent\Agent;

class MobileDetect extends Agent
{
    public function __construct(
        ?string $userAgent = null
    ) {
        parent::__construct();

        if ($userAgent) {
            $this->setUserAgent(
                $userAgent,
            );
        }
    }

    public function detectDevice(): array
    {
        return [
            'kind' => $this->getDeviceKind(),
            'model' => $this->device(),
            'is_mobile' => $this->isMobile(),
            'is_robot' => $this->isRobot(),
        ];
    }

    public function getDeviceKind(): string
    {
        $kind = 'unavailable';

        if ($this->isTablet()) {
            $kind = 'Tablet';
        } elseif ($this->isPhone()) {
            $kind = 'Phone';
        } elseif ($this->isComputer()) {
            $kind = 'Computer';
        }

        return $kind;
    }

    public function isPhone(
        $userAgent = null,
        $httpHeaders = null,
    ): bool {
        return !$this->isTablet() && !$this->isComputer();
    }

    public function isComputer(): bool
    {
        return !$this->isMobile();
    }
}
