<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers\Track;

use Stepanenko3\LaravelLogicContainers\Models\Track;
use Stepanenko3\LaravelLogicContainers\Models\Track\TrackerSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;

class Tracker
{
    /**
     * @var Illuminate\Foundation\Application
     */
    private $app;

    /**
     * User agent parser instance.
     */
    private ?\Stepanenko3\LaravelLogicContainers\Helpers\Track\UserAgentParser $userAgent = null;

    /**
     * Mobile detector instance.
     */
    private ?\Stepanenko3\LaravelLogicContainers\Helpers\Track\MobileDetect $mobileDetect = null;

    /**
     * Crawler detector instance.
     */
    private ?\Stepanenko3\LaravelLogicContainers\Helpers\Track\CrawlerDetect $crawlerDetect = null;

    /**
     * Language detector instance.
     */
    private ?\Stepanenko3\LaravelLogicContainers\Helpers\Track\LanguageDetect $langDetect = null;

    private $sessionId;

    private $userAgentOriginal;

    private $client_ip;

    private $isSecure;

    private $userId;

    public function __construct()
    {
        $this->app = app();
        $this->config = $this->app['config'];
    }

    public function parseData($request = null)
    {
        if (!$request) {
            $request = request();
        }

        return [
            'pageUrl' => $request->url(),
            'refererUrl' => $request->header('referer'),
            'sessionId' => $request->session()->getId(),
            'userAgent' => $request->userAgent(),
            'client_ip' => get_ip(),
            'isSecure' => $request->isSecure(),
            'userId' => user()?->id,
        ];
    }

    public function getData()
    {
        $res = $this->parseData();

        $this->bind(
            $res['sessionId'],
            $res['userAgent'],
            $res['client_ip'],
            $res['isSecure'],
            $res['userId'],
        );

        return [
            'client_ip' => $this->client_ip,
            'session_id' => $this->sessionId,
            'agent' => $this->getCurrentAgentArray(),
            'device' => $this->getCurrentDeviceArray(),
            'language' => $this->getLanguage(),
            'referrer' => $this->getReferrerArray($res['pageUrl'], $res['refererUrl']),
            'geo' => get_location($this->client_ip),
        ];
    }

    public function hitEvent($eventName, $pageUrl, $refererUrl = '')
    {
        $res = $this->parseData();

        $this->bind(
            $res['sessionId'],
            $res['userAgent'],
            $res['client_ip'],
            $res['isSecure'],
            $res['userId'],
        );

        if (!$this->userAgentOriginal || !$this->isTrackable($pageUrl) || !$this->config->get('tracker.log_events')) {
            return;
        }

        $agent = $this->hitAgent();
        $device = $this->hitDevice();
        $language = $this->hitLanguage();
        $referer = $this->hitReferer($refererUrl, $pageUrl);
        $geoip = $this->hitGeo();

        $session = $this->hitSession(
            optional($agent)->id,
            optional($device)->id,
            optional($referer)->id,
            optional($language)->id,
            optional($geoip)->id,
        );

        $path = $this->hitPath($pageUrl);
        $domain = $this->hitDomain($pageUrl);

        $event = Track\TrackerEvent::findOrCreateCached(['name' => $eventName], ['name']);

        $log = $this->hitEventLog(
            optional($event)->id,
            optional($session)->id,
            optional($referer)->id,
            optional($path)->id,
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

    public function hitPageVisit($pageUrl, $refererUrl, $sessionId, $userAgent, $client_ip, $isSecure = true, $userId = null)
    {
        $this->bind($sessionId, $userAgent, $client_ip, $isSecure, $userId);

        if (!$this->userAgentOriginal || !$this->isTrackable($pageUrl)) {
            return;
        }

        $agent = $this->hitAgent();
        $device = $this->hitDevice();
        $language = $this->hitLanguage();
        $referer = $this->hitReferer($refererUrl, $pageUrl);
        $geoip = $this->hitGeo();

        $session = $this->hitSession(
            optional($agent)->id,
            optional($device)->id,
            optional($referer)->id,
            optional($language)->id,
            optional($geoip)->id,
        );

        $path = $this->hitPath($pageUrl);
        $domain = $this->hitDomain($pageUrl);

        $log = $this->hitLog(
            optional($session)->id,
            optional($referer)->id,
            optional($path)->id,
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

    public function hitPath($pageUrl)
    {
        if (!$this->config->get('tracker.log_paths')) {
            return;
        }

        $parsed = parse_url((string) $pageUrl);

        if (!($parsed['path'] ?? '')) {
            return;
        }

        return Track\TrackerPath::findOrCreateCached(['path' => $parsed['path']], ['path']);
    }

    public function hitGeo()
    {
        if (!$this->config->get('tracker.log_geoip')) {
            return;
        }

        if ($session = session('tracker.country')) {
            $session['payload'] = json_encode($session, JSON_THROW_ON_ERROR);

            return Track\TrackerGeoip::findOrCreateCached($session, ['payload']);
        }

        $detect = get_location($this->client_ip);

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

            return Track\TrackerGeoip::findOrCreateCached($attributes, ['payload']);
        }
    }

    //

    public function getReferrerArray($refererUrl, $pageUrl)
    {
        if (!$refererUrl) {
            return;
        }

        $url = parse_url((string) $refererUrl);

        if (!isset($url['host'])) {
            return;
        }

        return new RefererParser($refererUrl, $pageUrl);
    }

    public function getCurrentAgentArray()
    {
        return [
            'name' => $name = $this->userAgentOriginal ?: 'Other',
            'browser' => optional($this->userAgent->ua)->family ?? '',
            'browser_version' => $this->userAgent->getUAVersion(),
            'name_hash' => hash('sha256', (string) $name),
        ];
    }

    public function getCurrentDeviceArray()
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

    public function getEventLogData($event_id, $session_id, $referer_id, $path_id)
    {
        return [
            'event_id' => $event_id,
            'session_id' => $session_id,
            'referer_id' => $referer_id,
            'path_id' => $path_id,
            'is_secure' => $this->isSecure,
        ];
    }

    public function getLogData($session_id, $referer_id, $path_id)
    {
        return [
            'session_id' => $session_id,
            'referer_id' => $referer_id,
            'path_id' => $path_id,
            'is_secure' => $this->isSecure,
        ];
    }

    public function isRobot()
    {
        return $this->crawlerDetect->isRobot();
    }

    public function getUserId()
    {
        return $this->config->get('tracker.log_users') ? $this->userId : null;
    }

    //

    public function isTrackable($pageUrl)
    {
        return $this->config->get('tracker.enabled', 0)
            && $this->logIsEnabled()
            && $this->isTrackableEnvironment()
            && $this->notRobotOrTrackable()
            && $this->isTrackableIp()
            && $this->pathIsTrackable($pageUrl);
    }

    public function logIsEnabled()
    {
        return $this->config->get('tracker.log_enabled')
            || $this->config->get('tracker.log_user_agents')
            || $this->config->get('tracker.log_devices')
            || $this->config->get('tracker.log_domains')
            || $this->config->get('tracker.log_languages')
            || $this->config->get('tracker.log_referers')
            || $this->config->get('tracker.log_sessions')
            || $this->config->get('tracker.log_events')
            //
            || $this->config->get('tracker.log_geoip')
            || $this->config->get('tracker.log_users')
            || $this->config->get('tracker.log_paths')
            || $this->config->get('tracker.log_queries');
    }

    public function isTrackableIp()
    {
        $trackable = !ipv4_in_range(
            $this->client_ip,
            $this->config->get('tracker.do_not_track_ips')
        );

        if (!$trackable) {
            // Debugbar::info($this->client_ip . ' is not trackable.');
        }

        return $trackable;
    }

    public function isTrackableEnvironment()
    {
        $trackable = !in_array(
            $this->app->environment(),
            $this->config->get('tracker.do_not_track_environments')
        );

        if (!$trackable) {
            // Debugbar::info('environment ' . $this->app->environment() . ' is not trackable.');
        }

        return $trackable;
    }

    public function pathIsTrackable($pageUrl)
    {
        $forbidden = $this->config->get('tracker.do_not_track_paths');

        $parsed = parse_url((string) $pageUrl);

        return !$forbidden || empty($parsed['path'] ?? '') || !in_array_wildcard($parsed['path'], $forbidden);
    }

    //

    public function makeCacheKey($attributes, $keys, $identifier)
    {
        $attributes = $this->extractAttributes($attributes);

        $cacheKey = "className={$identifier};";

        $keys = $this->extractKeys($attributes, $keys);

        foreach ($keys as $key) {
            if (isset($attributes[$key])) {
                $cacheKey .= "{$key}={$attributes[$key]};";
            }
        }

        return sha1($cacheKey);
    }

    private function notRobotOrTrackable()
    {
        $trackable = !$this->isRobot() || !$this->config->get('tracker.do_not_track_robots');

        if (!$trackable) {
            // Debugbar::info('tracking of robots is disabled.');
        }

        return $trackable;
    }

    private function bind($sessionId, $userAgent, $client_ip, $isSecure = true, $userId = null): void
    {
        $this->sessionId = $sessionId;
        $this->userAgentOriginal = $userAgent;
        $this->isSecure = $isSecure;
        $this->userId = $userId;

        if ($this->userAgentOriginal) {
            $this->client_ip = $client_ip;

            $this->userAgent = new UserAgentParser($this->userAgentOriginal);
            $this->mobileDetect = new MobileDetect($this->userAgentOriginal);
            $this->crawlerDetect = new CrawlerDetect($this->userAgentOriginal);
            $this->langDetect = new LanguageDetect($this->userAgentOriginal);
        }
    }

    private function hitAgent()
    {
        if (!$this->config->get('tracker.log_user_agents')) {
            return;
        }

        $attributes = $this->getCurrentAgentArray();

        return Track\TrackerAgent::findOrCreateCached($attributes, ['name']);
    }

    private function hitDevice()
    {
        if (!$this->config->get('tracker.log_devices')) {
            return;
        }

        $attributes = $this->getCurrentDeviceArray();

        return Track\TrackerDevice::findOrCreateCached($attributes, [
            'grade', 'family', 'model', 'brand', 'platform', 'platform_version',
        ]);
    }

    private function hitDomain($pageUrl)
    {
        if (!$this->config->get('tracker.log_domains')) {
            return;
        }

        $parsed = parse_url((string) $pageUrl);

        if (!isset($parsed['host'])) {
            return;
        }

        return Track\TrackerDomain::findOrCreateCached(['name' => $parsed['host']], ['name']);
    }

    private function hitLanguage()
    {
        if (!$this->config->get('tracker.log_languages')) {
            return;
        }

        $attributes = $this->getLanguage();

        return Track\TrackerLanguage::findOrCreateCached($attributes, ['preference', 'language_range']);
    }

    private function hitEventLog($event_id, $session_id, $referer_id, $path_id)
    {
        if (!$this->config->get('tracker.log_enabled')) {
            return;
        }

        $attributes = $this->getEventLogData($event_id, $session_id, $referer_id, $path_id);

        return Track\TrackerEventLog::create($attributes);
    }

    private function hitLog($session_id, $referer_id, $path_id)
    {
        if (!$this->config->get('tracker.log_enabled')) {
            return;
        }

        $attributes = $this->getLogData($session_id, $referer_id, $path_id);

        return Track\TrackerLog::create($attributes);
    }

    private function hitReferer($refererUrl, $pageUrl)
    {
        if (!$this->config->get('tracker.log_referers') || !$refererUrl) {
            return;
        }

        $parsed = $this->getReferrerArray($refererUrl, $pageUrl);

        if (!$parsed) {
            return;
        }

        $domain = $this->hitDomain($refererUrl);

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

        $referer = Track\TrackerReferer::findOrCreateCached($attributes, ['url', 'search_terms_hash']);

        if ($parsed->isKnown()) {
            $this->storeSearchTerms($referer, $parsed);
        }

        return $referer;
    }

    private function hitSession($agent_id, $device_id, $referer_id, $language_id, $geoip_id)
    {
        if (!$this->config->get('tracker.log_sessions')) {
            return;
        }

        $attributes = [
            'uuid' => $this->sessionId,
            'agent_id' => $agent_id,
            'device_id' => $device_id,
            'referer_id' => $referer_id,
            'language_id' => $language_id,
            'geoip_id' => $geoip_id,
            'client_ip' => $this->client_ip,
            'is_active' => 1,
            'last_activity' => now(),
            'is_robot' => $this->crawlerDetect->isRobot(),
            'robot' => $this->crawlerDetect->getMatches(),
        ];

        $session_id = Cookie::get(config('tracker.session_name'));
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

        $session = TrackerSession::where('uuid', $attributes['uuid'])->first();

        if (!$session) {
            $session = TrackerSession::where('client_ip', $attributes['client_ip'])
                ->where('device_id', $attributes['device_id'])
                ->where('agent_id', $attributes['agent_id'])
                ->first();
        }

        if ($session) {
            $session->fill($attributes)->save();
        } else {
            $session = new TrackerSession($attributes);
            $session->save();
        }

        // $session = TrackerSession::updateOrCreate([
        //     'client_ip' => $attributes['client_ip'],
        //     'device_id' => $attributes['device_id'],
        //     'agent_id' => $attributes['agent_id'],
        // ], $attributes);

        // $session = TrackerSession::findOrCreate($attributes, ['client_ip', 'device_id'], $created);

        return $session;
    }

    private function storeSearchTerms($referer, $parsed): void
    {
        foreach (explode(' ', (string) $parsed->getSearchTerm()) as $term) {
            $attributes = [
                'referer_id' => $referer->id,
                'search_term' => $term,
            ];

            Track\TrackerRefererSearchTerm::findOrCreateCached($attributes, ['referer_id', 'search_term']);
        }
    }

    //

    private function getLanguage()
    {
        return $this->langDetect->detectLanguage();
    }

    private function extractAttributes($attributes)
    {
        if (is_array($attributes) || is_string($attributes)) {
            return $attributes;
        }

        if (is_string($attributes) || is_numeric($attributes)) {
            return (array) $attributes;
        }

        if ($attributes instanceof Model) {
            return $attributes->getAttributes();
        }
    }

    private function extractKeys($attributes, $keys)
    {
        if (!$keys) {
            $keys = array_keys($attributes);
        }

        if (!is_array($keys)) {
            return (array) $keys;
        }

        return $keys;
    }
}
