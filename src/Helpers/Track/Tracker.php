<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers\Track;

use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerAgent;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerDevice;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerDomain;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerEvent;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerEventLog;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerGeoip;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerLanguage;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerLog;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerPath;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerReferer;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerRefererSearchTerm;

class Tracker
{
    private ?UserAgentParser $userAgent = null;
    private ?MobileDetect $mobileDetect = null;
    private ?CrawlerDetect $crawlerDetect = null;
    private ?LanguageDetect $langDetect = null;

    private string $sessionId;
    private string $userAgentOriginal;
    private string $clientIp;
    private bool $isSecure;
    private ?int $userId;

    public function parseData(
        ?Request $request = null,
    ): array {
        $request ??= request();

        return [
            'pageUrl' => $request->url(),
            'refererUrl' => $request->header(
                key: 'referer',
            ),
            'sessionId' => $request->session()->getId(),
            'userAgent' => $request->userAgent(),
            'clientIp' => get_ip(),
            'isSecure' => $request->isSecure(),
            'userId' => user()?->id,
        ];
    }

    public function getData(): array
    {
        $res = $this->parseData();

        $this->bind(
            sessionId: $res['sessionId'],
            userAgent: $res['userAgent'],
            clientIp: $res['clientIp'],
            isSecure: $res['isSecure'],
            userId: $res['userId'],
        );

        return [
            'clientIp' => $this->clientIp,
            'session_id' => $this->sessionId,
            'agent' => $this->getCurrentAgentArray(),
            'device' => $this->getCurrentDeviceArray(),
            'language' => $this->getLanguage(),
            'referrer' => $this->getReferrerArray(
                refererUrl: $res['pageUrl'],
                pageUrl: $res['refererUrl'],
            ),
            'geo' => get_location(
                ip: $this->clientIp,
            ),
        ];
    }

    public function hitEvent(
        string $eventName,
        string $pageUrl,
        string $refererUrl = '',
    ): array {
        $res = $this->parseData();

        $this->bind(
            sessionId: $res['sessionId'],
            userAgent: $res['userAgent'],
            clientIp: $res['clientIp'],
            isSecure: $res['isSecure'],
            userId: $res['userId'],
        );

        if (!$this->userAgentOriginal || !$this->isTrackable($pageUrl) || !config('tracker.log_events')) {
            return [];
        }

        $agent = $this->hitAgent();
        $device = $this->hitDevice();
        $language = $this->hitLanguage();
        $referer = $this->hitReferer(
            refererUrl: $refererUrl,
            pageUrl: $pageUrl,
        );
        $geoip = $this->hitGeo();

        $session = $this->hitSession(
            agent_id: $agent?->id,
            device_id: $device?->id,
            referer_id: $referer?->id,
            language_id: $language?->id,
            geoip_id: $geoip?->id,
        );

        $path = $this->hitPath(
            pageUrl: $pageUrl,
        );

        $domain = $this->hitDomain(
            pageUrl: $pageUrl,
        );

        $event = TrackerEvent::findOrCreateCached(
            attributes: [
                'name' => $eventName,
            ],
            keys: [
                'name',
            ],
        );

        $log = $this->hitEventLog(
            event_id: $event?->id,
            session_id: $session?->id,
            referer_id: $referer?->id,
            path_id: $path?->id,
        );

        return [
            'agent' => $agent,
            'device' => $device,
            'language' => $language,
            'referer' => $referer,
            'session' => $session,
            'geoip' => $geoip,

            'path' => $path ?? null,
            'domain' => $domain ?? null,
            'log' => $log ?? null,
        ];
    }

    public function hitPageVisit(
        string $pageUrl,
        string $refererUrl,
        string $sessionId,
        string $userAgent,
        string $clientIp,
        bool $isSecure = true,
        ?int $userId = null
    ): array {
        $this->bind(
            sessionId: $sessionId,
            userAgent: $userAgent,
            clientIp: $clientIp,
            isSecure: $isSecure,
            userId: $userId
        );

        if (!$this->userAgentOriginal || !$this->isTrackable($pageUrl)) {
            return [];
        }

        $agent = $this->hitAgent();
        $device = $this->hitDevice();
        $language = $this->hitLanguage();
        $referer = $this->hitReferer(
            refererUrl: $refererUrl,
            pageUrl: $pageUrl,
        );
        $geoip = $this->hitGeo();

        $session = $this->hitSession(
            agent_id: $agent?->id,
            device_id: $device?->id,
            referer_id: $referer?->id,
            language_id: $language?->id,
            geoip_id: $geoip?->id,
        );

        $path = $this->hitPath(
            pageUrl: $pageUrl,
        );

        $domain = $this->hitDomain(
            pageUrl: $pageUrl,
        );

        $log = $this->hitLog(
            session_id: $session?->id,
            referer_id: $referer?->id,
            path_id: $path?->id,
        );

        return [
            'agent' => $agent,
            'device' => $device,
            'language' => $language,
            'referer' => $referer,
            'session' => $session,
            'geoip' => $geoip,

            'path' => $path ?? null,
            'domain' => $domain ?? null,
            'log' => $log ?? null,
        ];
    }

    public function hitPath(
        string $pageUrl,
    ): ?TrackerPath {
        if (!config('tracker.log_paths')) {
            return null;
        }

        $parsed = parse_url(
            url: (string) $pageUrl,
        );

        if (!($parsed['path'] ?? '')) {
            return null;
        }

        return TrackerPath::findOrCreateCached(
            attributes: [
                'path' => $parsed['path'],
            ],
            keys: [
                'path',
            ],
        );
    }

    public function hitGeo(): ?TrackerGeoip
    {
        if (!config('tracker.log_geoip')) {
            return null;
        }

        if ($session = session('tracker.country')) {
            $session['payload'] = json_encode($session, JSON_THROW_ON_ERROR);

            return TrackerGeoip::findOrCreateCached(
                attributes: $session,
                keys: [
                    'payload',
                ],
            );
        }

        $detect = get_location(
            ip: $this->clientIp,
        );

        if ($detect) {
            $attributes = [
                'continent_code' => $detect['continent_code'],
                'continent_name' => $detect['continent_name'],
                'country_code' => $detect['country_code'],
                'country_name' => $detect['country_name'],
                'region_code' => $detect['region_code'],
                'region_name' => $detect['region_name'],
                'city' => $detect['city'],
                'latitude' => $detect['latitude'],
                'longitude' => $detect['longitude'],
                'zip' => $detect['zip'],
            ];

            session(['tracker.country' => $attributes]);

            $attributes['payload'] = md5(json_encode($attributes, JSON_THROW_ON_ERROR));

            return TrackerGeoip::findOrCreateCached(
                attributes: $attributes,
                keys: [
                    'payload',
                ],
            );
        }

        return null;
    }

    //

    public function getReferrerArray(
        string $refererUrl,
        string $pageUrl,
    ): ?RefererParser {
        if (!$refererUrl) {
            return null;
        }

        $url = parse_url(
            url: (string) $refererUrl,
        );

        if (!isset($url['host'])) {
            return null;
        }

        return new RefererParser(
            refererUrl: $refererUrl,
            pageUrl: $pageUrl,
        );
    }

    public function getCurrentAgentArray(): array
    {
        return [
            'name' => $name = $this->userAgentOriginal ?: 'Other',
            'browser' => $this->userAgent->ua?->family ?? '',
            'browser_version' => $this->userAgent->getUAVersion(),
            'name_hash' => hash('sha256', (string) $name),
        ];
    }

    public function getCurrentDeviceArray(): array
    {
        return [
            'family' => $this->userAgent->device->family,
            'model' => $this->userAgent->device->model,
            'brand' => $this->userAgent->device->brand,
            'platform' => $this->userAgent->os->family,
            'platform_version' => $this->userAgent->getOSVersion(),
            'is_phone' => $this->mobileDetect->isMobile(),
            'is_tablet' => $this->mobileDetect->isTablet(),
            'is_desktop' => $this->mobileDetect->isDesktop(),
            'grade' => $this->mobileDetect->mobileGrade(),
        ];
    }

    public function getEventLogData(
        int $event_id,
        int $session_id,
        int $referer_id,
        int $path_id,
    ): array {
        return [
            'event_id' => $event_id,
            'session_id' => $session_id,
            'referer_id' => $referer_id,
            'path_id' => $path_id,
            'is_secure' => $this->isSecure,
        ];
    }

    public function getLogData(
        int $session_id,
        int $referer_id,
        int $path_id,
    ): array {
        return [
            'session_id' => $session_id,
            'referer_id' => $referer_id,
            'path_id' => $path_id,
            'is_secure' => $this->isSecure,
        ];
    }

    public function isRobot(): bool
    {
        return $this->crawlerDetect->isRobot();
    }

    public function getUserId(): ?int
    {
        return config('tracker.log_users')
            ? $this->userId
            : null;
    }

    //

    public function isTrackable(
        string $pageUrl,
    ): bool {
        return config('tracker.enabled', 0)
            && $this->logIsEnabled()
            && $this->isTrackableEnvironment()
            && $this->notRobotOrTrackable()
            && $this->isTrackableIp()
            && $this->pathIsTrackable($pageUrl);
    }

    public function logIsEnabled(): bool
    {
        return config('tracker.log_enabled')
            || config('tracker.log_user_agents')
            || config('tracker.log_devices')
            || config('tracker.log_domains')
            || config('tracker.log_languages')
            || config('tracker.log_referers')
            || config('tracker.log_sessions')
            || config('tracker.log_events')
            //
            || config('tracker.log_geoip')
            || config('tracker.log_users')
            || config('tracker.log_paths')
            || config('tracker.log_queries');
    }

    public function isTrackableIp(): bool
    {
        $trackable = !ipv4_in_range(
            ip: $this->clientIp,
            range: config('tracker.do_not_track_ips'),
        );

        if (!$trackable) {
            // Debugbar::info($this->clientIp . ' is not trackable.');
        }

        return $trackable;
    }

    public function isTrackableEnvironment(): bool
    {
        $trackable = !in_array(
            config('app.env'),
            config('tracker.do_not_track_environments')
        );

        if (!$trackable) {
            // Debugbar::info('environment ' . $this->app->environment() . ' is not trackable.');
        }

        return $trackable;
    }

    public function pathIsTrackable(
        string $pageUrl,
    ): bool {
        $forbidden = config('tracker.do_not_track_paths');

        $parsed = parse_url(
            url: (string) $pageUrl,
        );

        return !$forbidden || empty($parsed['path'] ?? '') || !in_array_wildcard(
            needle: $parsed['path'],
            haystack: $forbidden,
        );
    }

    //

    public function makeCacheKey(
        array $attributes,
        array $keys,
        string $identifier,
    ): string {
        $attributes = $this->extractAttributes(
            attributes: $attributes,
        );

        $cacheKey = "className={$identifier};";

        $keys = $this->extractKeys(
            attributes: $attributes,
            keys: $keys,
        );

        foreach ($keys as $key) {
            if (isset($attributes[$key])) {
                $cacheKey .= "{$key}={$attributes[$key]};";
            }
        }

        return sha1($cacheKey);
    }

    private function notRobotOrTrackable(): bool
    {
        $trackable = !$this->isRobot() || !config('tracker.do_not_track_robots');

        if (!$trackable) {
            // Debugbar::info('tracking of robots is disabled.');
        }

        return $trackable;
    }

    private function bind(
        int $sessionId,
        string $userAgent,
        string $clientIp,
        bool $isSecure = true,
        ?int $userId = null,
    ): void {
        $this->sessionId = $sessionId;
        $this->userAgentOriginal = $userAgent;
        $this->isSecure = $isSecure;
        $this->userId = $userId;

        if ($this->userAgentOriginal) {
            $this->clientIp = $clientIp;

            $this->userAgent = new UserAgentParser(
                userAgent: $this->userAgentOriginal,
            );

            $this->mobileDetect = new MobileDetect(
                userAgent: $this->userAgentOriginal,
            );

            $this->crawlerDetect = new CrawlerDetect(
                userAgent: $this->userAgentOriginal,
            );

            $this->langDetect = new LanguageDetect(
                userAgent: $this->userAgentOriginal,
            );
        }
    }

    private function hitAgent(): ?TrackerAgent
    {
        if (!config('tracker.log_user_agents')) {
            return null;
        }

        $attributes = $this->getCurrentAgentArray();

        return TrackerAgent::findOrCreateCached(
            attributes: $attributes,
            keys: [
                'name',
            ],
        );
    }

    private function hitDevice(): ?TrackerDevice
    {
        if (!config('tracker.log_devices')) {
            return null;
        }

        $attributes = $this->getCurrentDeviceArray();

        return TrackerDevice::findOrCreateCached(
            attributes: $attributes,
            keys: [
                'grade',
                'family',
                'model',
                'brand',
                'platform',
                'platform_version',
            ],
        );
    }

    private function hitDomain(
        string $pageUrl,
    ): ?TrackerDomain {
        if (!config('tracker.log_domains')) {
            return null;
        }

        $parsed = parse_url(
            url: (string) $pageUrl,
        );

        if (!isset($parsed['host'])) {
            return null;
        }

        return TrackerDomain::findOrCreateCached(
            attributes: [
                'name' => $parsed['host'],
            ],
            keys: [
                'name',
            ]
        );
    }

    private function hitLanguage(): ?TrackerLanguage
    {
        if (!config('tracker.log_languages')) {
            return null;
        }

        $attributes = $this->getLanguage();

        return TrackerLanguage::findOrCreateCached(
            attributes: $attributes,
            keys: [
                'preference',
                'language_range',
            ]
        );
    }

    private function hitEventLog(
        int $event_id,
        int $session_id,
        int $referer_id,
        int $path_id,
    ): ?TrackerEventLog {
        if (!config('tracker.log_enabled')) {
            return null;
        }

        $attributes = $this->getEventLogData(
            event_id: $event_id,
            session_id: $session_id,
            referer_id: $referer_id,
            path_id: $path_id,
        );

        return TrackerEventLog::query()
            ->create(
                attributes: $attributes,
            );
    }

    private function hitLog(
        int $session_id,
        int $referer_id,
        int $path_id,
    ): ?TrackerLog {
        if (!config('tracker.log_enabled')) {
            return null;
        }

        $attributes = $this->getLogData(
            session_id: $session_id,
            referer_id: $referer_id,
            path_id: $path_id,
        );

        return TrackerLog::create(
            attributes: $attributes,
        );
    }

    private function hitReferer(
        string $refererUrl,
        string $pageUrl,
    ): ?TrackerReferer {
        if (!config('tracker.log_referers') || !$refererUrl) {
            return null;
        }

        $parsed = $this->getReferrerArray(
            refererUrl: $refererUrl,
            pageUrl: $pageUrl,
        );

        if (!$parsed) {
            return null;
        }

        $domain = $this->hitDomain(
            pageUrl: $refererUrl,
        );

        $attributes = [
            'url' => $refererUrl,
            'domain_id' => $domain->id,
            'medium' => null,
            'source' => null,
            'search_term' => null,
            'search_terms_hash' => null,
        ];

        if ($parsed->isKnown()) {
            $attributes['medium'] = $parsed->getMedium();
            $attributes['source'] = $parsed->getSource();
            $attributes['search_term'] = $parsed->getSearchTerm();
            $attributes['search_terms_hash'] = sha1((string) $parsed->getSearchTerm());
        }

        $referer = TrackerReferer::findOrCreateCached(
            attributes: $attributes,
            keys: [
                'url',
                'search_terms_hash',
            ]
        );

        if ($parsed->isKnown()) {
            $this->storeSearchTerms(
                referer: $referer,
                parsed: $parsed,
            );
        }

        return $referer;
    }

    private function hitSession(
        int $agent_id,
        int $device_id,
        int $referer_id,
        int $language_id,
        int $geoip_id,
    ): ?TrackerSession {
        if (!config('tracker.log_sessions')) {
            return null;
        }

        $attributes = [
            'uuid' => $this->sessionId,
            'agent_id' => $agent_id,
            'device_id' => $device_id,
            'referer_id' => $referer_id,
            'language_id' => $language_id,
            'geoip_id' => $geoip_id,
            'client_ip' => $this->clientIp,
            'is_active' => 1,
            'last_activity' => now(),
            'is_robot' => $this->crawlerDetect->isRobot(),
            'robot' => $this->crawlerDetect->getMatches(),
        ];

        $session_id = Cookie::get(
            key: config('tracker.session_name'),
        );

        if ((int) $session_id) {
            $attributes['id'] = (int) $session_id;
        }

        $user_id = $this->getUserId();

        if ($user_id) {
            $attributes['user_id'] = $user_id;
        }

        // if ($this->userId {
        //     $attributes['last_activity'] = now();
        // }

        $session = TrackerSession::query()
            ->where(
                column: 'uuid',
                operator: '=',
                value: $attributes['uuid'],
            )
            ->first();

        if (!$session) {
            $session = TrackerSession::query()
                ->where(
                    column: 'client_ip',
                    operator: '=',
                    value: $attributes['client_ip'],
                )
                ->where(
                    column: 'device_id',
                    operator: '=',
                    value: $attributes['device_id'],
                )
                ->where(
                    column: 'agent_id',
                    operator: '=',
                    value: $attributes['agent_id'],
                )
                ->first();
        }

        if ($session) {
            $session
                ->fill(
                    attributes: $attributes,
                )
                ->save();
        } else {
            $session = new TrackerSession(
                attributes: $attributes,
            );
            $session->save();
        }

        // $session = TrackerSession::updateOrCreate([
        //     'clientIp' => $attributes['client_ip'],
        //     'device_id' => $attributes['device_id'],
        //     'agent_id' => $attributes['agent_id'],
        // ], $attributes);

        // $session = TrackerSession::findOrCreate($attributes, ['client_ip', 'device_id'], $created);

        return $session;
    }

    private function storeSearchTerms(
        ?TrackerReferer $referer,
        ?RefererParser $parsed,
    ): void {
        $terms = explode(' ', (string) $parsed->getSearchTerm());

        foreach ($terms as $term) {
            $attributes = [
                'referer_id' => $referer->id,
                'search_term' => $term,
            ];

            TrackerRefererSearchTerm::findOrCreateCached(
                attributes: $attributes,
                keys: [
                    'referer_id',
                    'search_term',
                ],
            );
        }
    }

    //

    private function getLanguage(): array
    {
        return $this->langDetect->detectLanguage();
    }

    private function extractAttributes(
        array | string | int | float $attributes,
    ): array | string | null {
        if (is_array($attributes) || is_string($attributes)) {
            return $attributes;
        }

        if (is_string($attributes) || is_numeric($attributes)) {
            return (array) $attributes;
        }

        if ($attributes instanceof Model) {
            return $attributes->getAttributes();
        }

        return null;
    }

    private function extractKeys(
        array $attributes,
        array $keys,
    ): array {
        if (!$keys) {
            $keys = array_keys($attributes);
        }

        if (!is_array($keys)) {
            return (array) $keys;
        }

        return $keys;
    }
}
