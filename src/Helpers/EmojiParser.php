<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class EmojiParser
{
    public const URL = 'https://unicode.org/Public/emoji/%s/emoji-test.txt';

    /**
     * @var string
     */
    private $dir;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array
     */
    private $options = [
        'sort' => null,
        'filename' => 'emoji-%s',
    ];

    /**
     * EmojiParser constructor.
     *
     * @param string $dir dir of emoji images, json and txt files
     * @param string $version unicode emojis version
     */
    public function __construct(string $dir, string $version = '13.1', array $options = [])
    {
        $this->version = $version;
        $this->dir = $dir;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Parse emoji-test.txt.
     *
     * @return array
     */
    public function parse()
    {
        $contents = $this->load();

        $blocks = explode("\n\n", trim($contents));

        $result = $this->parseHeader(array_shift($blocks));
        $result['emoji'] = $this->parseBody($blocks, $result['version']);

        return $result;
    }

    public function upload(): void
    {
        $data = $this->parse();
        $file_path = $this->getFilePath('json');

        foreach ([160] as $size) {
            $this->uploadImages($data, $size);
        }

        $data['emoji'] = array_filter($data['emoji'], fn ($emoji) => file_exists($this->getImgPath($emoji['code'], 160)));

        file_put_contents($file_path, json_encode($data));
    }

    public function resizeImages($src = 160, $sizes = [16, 32, 64, 128]): void
    {
        $files = Storage::disk('public')->files('emoji/img/' . $src);

        foreach ($sizes as $size) {
            foreach ($files as $file) {
                Image::make(storage_path('/app/public/' . $file))
                    ->resize($size, null, fn ($constraint) => $constraint->aspectRatio())
                    ->save(str_ireplace('/160/', '/' . $size . '/', storage_path('/app/public/' . $file)));

                dump('resize: ' . $file . ', to: ' . str_ireplace('/160/', '/' . $size . '/', $file));
            }
        }
    }

    private function parseHeader(string $block)
    {
        $result = [];
        $rows = explode("\n", $block);
        foreach ($rows as $row) {
            if (($value = $this->getValue($row, '# Date:')) !== false) {
                $result['date'] = $value;
            }
            if (($value = $this->getValue($row, '# Version:')) !== false) {
                $result['version'] = $value;
                $result['url'] = sprintf(self::URL, $value);
            }
        }

        return $result;
    }

    private function parseBody(array $blocks, $emojiVersion)
    {
        $result = [];
        $group = null;
        foreach ($blocks as $block) {
            $rows = explode("\n", trim($block));

            if (($value = $this->getValue($rows[0], '# group:')) !== false) {
                $group = $value;

                continue;
            }

            if (($subgroup = $this->getValue($rows[0], '# subgroup:')) !== false) {
                array_shift($rows);
                $subgroups = [];
                foreach ($rows as $row) {
                    if ($emojiVersion >= 12.1) {
                        // Format: code points; status # emoji EX.X name
                        [$codePoint, $status, $emoji, $version, $name] = sscanf($row, '%[^;]; %[^#] # %[^ ] E%[^ ] %[^$]');
                    } else {
                        // Format: code points; status # emoji name
                        [$codePoint, $status, $emoji, $name] = sscanf($row, '%[^;]; %[^#] # %[^ ] %[^$]');
                        $version = null;
                    }

                    $subgroups[] = [
                        'group' => $group,
                        'subgroup' => $subgroup,
                        'code' => trim($codePoint),
                        // 'status' => trim($status),
                        'emoji' => trim($emoji),
                        'name' => trim($name),
                        // 'version' => (float)trim($version),
                    ];
                }

                if ($this->options['sort'] !== null) {
                    $subgroups = $this->sort($subgroups, 'emoji');
                }

                $result = array_merge($result, $subgroups);
            }
        }

        return $result;
    }

    private function uploadImages($data, $size = 160): void
    {
        foreach ($data['emoji'] as $emoji) {
            $imgPath = $this->getImgPath($emoji['code'], $size);

            if (file_exists($imgPath)) {
                continue;
            }

            $code = '';

            ecollect(preg_split('/(:|,) /', trim($emoji['name'])))
                ->map(fn ($part) => Str::slug($part))
                ->sort(fn ($part) => mb_stripos($part, 'tone') ? 1 : -1)
                ->map(function ($part) use (&$code): void {
                    $code .= (mb_stripos($part, 'tone') ? '_' : '-') . $part;
                });

            $code = trim($code, '-_');
            $code .= '_' . mb_strtolower(Str::slug(trim($emoji['code'])));

            $code_parts = explode(' ', trim($emoji['code']));
            if (count($code_parts) >= 2 && mb_stripos(trim($emoji['name']), ': ') !== false) {
                $code .= '_' . mb_strtolower(last($code_parts));
            }

            $url = 'https://emojigraph.org/media/apple/' . Str::slug($emoji['name']) . '_' . mb_strtolower(Str::slug(trim($emoji['code']))) . '.png';

            if ($img = @file_get_contents($url)) {
                file_put_contents($imgPath, $img);
            }

            foreach ([16, 32, 64, 128] as $subsize) {
                Image::make('public/foo.jpg')->resize(320, 240)->insert('public/watermark.png');
            }
        }

        $this->resizeImages($size);
    }

    private function sort(array $array, string $key)
    {
        foreach ($array as $row) {
            $sort[] = $row[$key];
        }
        array_multisort($sort, $this->options['sort'], $array);

        return $array;
    }

    private function getValue($row, $key)
    {
        if (!str_starts_with($row, $key)) {
            return false;
        }

        return substr($row, strlen($key) + 1);
    }

    private function getImgPath($code, $size = 160)
    {
        return $this->dir . '/img/' . $size . '/' . Str::slug($code) . '.png';
    }

    private function getFilePath($ext = 'txt')
    {
        return $this->dir . '/' . sprintf($this->options['filename'], $this->version) . '.' . $ext;
    }

    private function load()
    {
        $file_path = $this->getFilePath('txt');
        if (!is_readable($file_path)) {
            file_put_contents($file_path, file_get_contents(sprintf(self::URL, $this->version)));
        }

        return file_get_contents($file_path);
    }
}
