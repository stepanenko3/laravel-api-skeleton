<?php

use Illuminate\Support\Facades\{Http, Storage};
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Stepanenko3\LaravelApiSkeleton\Helpers\ExtendedCollection;
use Stepanenko3\LaravelApiSkeleton\Models\Track\TrackerGeoipCache;

if (!function_exists('get_media_path')) {
    function get_media_path(
        Media $media,
        string $conversion = '',
    ): string {
        $pathGenerator = app(config('media-library.path_generator'));

        $baseFileName = pathinfo(
            (string) $media->file_name,
            PATHINFO_FILENAME,
        );

        if ($conversion) {
            $model = new $media->model_type();
            $fileExtension = $model->getConversionExtension(
                $conversion,
            );

            $fileName = substr(base_convert(md5($baseFileName), 16, 32), 0, 12) . '-' . $conversion;

            $path = $pathGenerator->getPathForConversions($media);
        } else {
            $fileExtension = pathinfo((string) $media->file_name, PATHINFO_EXTENSION);

            $fileName = $baseFileName;

            $path = $pathGenerator->getPath($media);
        }

        return "{$path}{$fileName}.{$fileExtension}";
    }
}

if (!function_exists('get_media_url')) {
    function get_media_url(
        Media $media,
        string $conversion = '',
        bool $version = false,
        bool $absolute = true,
    ): string {
        $url = get_media_path($media, $conversion);

        if ($version) {
            $url = "{$url}?v={$media->updated_at->timestamp}";
        }

        $url = Storage::disk(config('media-library.disk_name'))->url($url);

        if ($absolute) {
            $url = url($url);
        }

        return $url;
    }
}

if (!function_exists('process_text_editor')) {
    function process_text_editor(
        string | array $field,
    ): array | string {
        if (is_array($field)) {
            foreach ($field as $lang => $body) {
                $field[$lang] = str_ireplace([
                    '<p data-f-id="pbf" style="text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;">Powered by <a href="https://www.froala.com/wysiwyg-editor?pb=1" title="Froala Editor">Froala Editor</a></p>',
                    '<p data-f-id=\"pbf\" style=\"text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;\">Powered by <a href=\"https:\/\/www.froala.com\/wysiwyg-editor?pb=1\" title=\"Froala Editor\">Froala Editor<\/a><\/p>',
                ], '', (string) $body);
            }
        } else {
            $field = str_ireplace([
                '<p data-f-id="pbf" style="text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;">Powered by <a href="https://www.froala.com/wysiwyg-editor?pb=1" title="Froala Editor">Froala Editor</a></p>',
                '<p data-f-id=\"pbf\" style=\"text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;\">Powered by <a href=\"https:\/\/www.froala.com\/wysiwyg-editor?pb=1\" title=\"Froala Editor\">Froala Editor<\/a><\/p>',
            ], '', $field);
        }

        return $field;
    }
}

if (!function_exists('make_body')) {
    function make_body(
        string $body,
        mixed $tinyfade = null,
        bool $lazyLoad = true,
        string $target = '_blank',
        string $rel = 'nofollow noopener noreferrer',
        bool $closeTags = true,
        bool $replaceHeaders = false,
        bool $removeSpaces = true,
        bool $removeEmptyTags = true
    ): string {
        $body = str_ireplace('tablesaw', 'table', $body);

        if ($removeEmptyTags) {
            $body = preg_replace('/<([a-z]+)[^>]*>(\s)*(&nbsp;)*(<br[^>]*\/?>)*(\s)*<\/\1[^>]*>/i', '', $body);
        }

        // Replace images to lazy load
        if ($lazyLoad) {
            if ($tinyfade) {
                $body = preg_replace('/<img\s?([^>]*) src="([^>^"]*)"([^>]*)\/?>/i', '<img $1 tinyfade="' . $tinyfade . '" tinyfade-img="$2" data-src="$2"$3>', $body);
            }

            $body = preg_replace('/<img\s?([^>]*) class="([^>^"]*)"([^>]*)\/?>/i', '<img $1 class="$2 lazy"$3>', $body);
            $body = preg_replace('/<img\s?(?![^>]*class)([^>^"]*)\/?>/i', '<img $1 class="lazy">', $body);
        }

        // Add target="_blank" to links
        if ($target) {
            $body = preg_replace('/<a(?=\s|>)(?![^>]*target)([^>]*)>/i', '<a $1 target="' . $target . '">', $body);
        }

        // Add rel="nofollow noopener noreferrer" to links
        if ($rel) {
            $body = preg_replace('/<a(?=\s|>)(?![^>]*rel)([^>]*?)>/i', '<a $1 rel="' . $rel . '">', $body);
        }

        // Replace <h2>text</h2> to <p class="h2 ">text</p>
        if ($replaceHeaders) {
            $body = preg_replace('/<h([1-6])\s?(class="(.*?)")?(.*?)>(.*?)<\/h[1-6]>/i', '<p class="h${1} ${3}"${4}>${5}</p>', $body);
        }

        // Close noclosed tags
        if ($closeTags) {
            $body = closetags($body);
        }

        // Remove empty content
        if ($removeSpaces) {
            $body = preg_replace('/(<\/?br\ ?\/?>)+/', '<br/>', $body);
        }

        return process_text_editor($body);
    }
}

if (!function_exists('make_layouts')) {
    function make_layouts(
        array $layouts,
    ): ExtendedCollection {
        $locale = config('app.locale');

        return ecollect($layouts)
            ->map(function ($item) use ($locale) {
                $attributes = [];

                foreach ($item['attributes'] as $key => $attr) {
                    if (is_array($attr)) {
                        if (in_array($locale, array_keys($attr))) {
                            $attributes[$key] = $attr[$locale] ?? '-';
                        } elseif (is_array($attr[0] ?? null)) {
                            $attributes[$key] = make_layouts($attr);
                        } else {
                            $attributes[$key] = $attr;
                        }
                    } else {
                        $attributes[$key] = $attr;
                    }
                }

                return [
                    'layout' => $item['layout'],
                    'attributes' => $attributes,
                ];
            });
    }
}

if (!function_exists('geocode')) {
    function geocode(
        string $address,
        int | float $lat = 0,
        int | float $lng = 0,
    ) {
        $geo = null;
        $geo_type = '';

        $latlng = implode(
            separator: ',',
            array: [$lat, $lng],
        );

        if ($address) {
            $geo_type = 'address';
        } elseif ($lat && $lng) {
            $geo_type = 'latlng';
        }

        if ($geo_type) {
            $lang = str_replace(
                ['_', 'ua'],
                ['-', 'uk'],
                (string) config('app.locale'),
            );

            $googleParam = $geo_type == 'address' ? 'address=' . urlencode((string) $address) : 'latlng=' . $latlng;
            $googleLink = 'https://maps.googleapis.com/maps/api/geocode/json?' . $googleParam . '&sensor=false&key=' . env('GEOCODE_KEY') . '&language=' . $lang;

            $res = Http::get(
                url: $googleLink,
            );

            if ($res->ok()) {
                $geo_res = $res['results'][0];

                $geo = $geo_res['geometry']['location'];
                $geo_res = ecollect($geo_res['address_components']);

                $sort_arr = ['route', 'street_number'];
                $geo_name = $geo_res
                    ->filter(function ($value, $key) use ($sort_arr) {
                        $count = is_countable($value['types']) ? count($value['types']) : 0;

                        return count(array_diff($value['types'], $sort_arr)) < $count;
                    })
                    ->sortByDesc(fn ($val, $key) => $key)
                    ->pluck('long_name')
                    ->implode(', ');

                $geo['address'] = $geo_name;
            }
        }

        return (object) $geo;
    }
}

if (!function_exists('get_location')) {
    function get_location(
        ?string $ip = null,
    ): ?array {
        if (!$ip) {
            $ip = get_ip();
        }

        $cache = TrackerGeoipCache::query()
            ->firstWhere(
                column: 'client_ip',
                operator: '=',
                value: $ip,
            );

        if ($cache && $cache->payload) {
            return $cache->payload;
        }

        if (in_array_wildcard(
            needle: $ip,
            haystack: [
                '127.0.0.1',
                '::1',
                '192.168.*',
            ],
        )) {
            return null;
        }

        $res = Http::accept(
            contentType: 'application/json',
        )
            ->get(
                url: 'http://ipinfo.io/' . $ip,
                query: [
                    'language' => 'ru',
                ],
            )
            ->json();

        if ($res['loc'] ?? null) {
            $loc = explode(
                separator: ',',
                string: (string) $res['loc'],
            );

            $data = [
                'continent_code' => '',
                'continent_name' => '',
                'country_code' => $res['country'] ?? '',
                'country_name' => '',
                'region_code' => '',
                'region_name' => $res['region'] ?? '',
                'city' => $res['city'],
                'latitude' => $loc[0] ?? '',
                'longitude' => $loc[1] ?? '',
                'zip' => $res['postal'] ?? '',
            ];

            TrackerGeoipCache::create([
                'client_ip' => $ip,
                'payload' => $data,
            ]);
        }

        return $data ?? [];
    }
}

if (!function_exists('ecollect')) {
    function ecollect(
        mixed $items = [],
    ): ExtendedCollection {
        return new ExtendedCollection(
            $items,
        );
    }
}
if (!function_exists('array_values_dot')) {
    function array_values_dot(
        array $fields,
        string $prepend = '',
    ) {
        $result = [];

        foreach ($fields as $key => $field) {
            $prefix = is_numeric($key) ? '' : $key . '.';

            if (is_array($field)) {
                $result = array_merge(
                    $result,
                    array_values_dot(
                        fields: $field,
                        prepend: $prepend . $prefix,
                    ),
                );
            } else {
                $result[] = $prepend . $prefix . $field;
            }
        }

        return $result;
    }
}
