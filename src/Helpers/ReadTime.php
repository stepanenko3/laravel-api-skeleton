<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Exception;

class ReadTime implements \Stringable
{
    /**
     * Whether or not minutes/seconds should be abbreviated as min/sec.
     *
     * @var bool
     */
    public $abbreviated;

    /**
     * The string content to evaluate.
     *
     * @var string
     */
    public $content;

    /**
     * The direction the language reads. Default ltr is true.
     *
     * @var bool
     */
    public $ltr;

    /**
     * Omit seconds from being displayed in the read time estimate.
     *
     * @var bool
     */
    public $omitSeconds;

    /**
     * The average words read per minute.
     *
     * @var (int)
     */
    public $wordsPerMinute;

    /**
     * An array containing the read time estimate data.
     */
    private ?array $estimate = null;

    /**
     * The sum total number of words in the content.
     */
    private readonly int $wordsInContent;

    public function __construct($content, bool $omitSeconds = true, bool $abbreviated = false, int $wordsPerMinute = 230)
    {
        $this->abbreviated = $abbreviated;
        $this->content = $this->parseContent($content);
        $this->ltr = true;
        $this->omitSeconds = $omitSeconds;
        $this->wordsInContent = $this->wordsCount($this->content);
        $this->wordsPerMinute = $wordsPerMinute;
    }

    public function __toString(): string
    {
        return $this->get();
    }

    public function __invoke()
    {
        return $this->get();
    }

    /**
     * Abbreviate the minutes/seconds.
     *
     * @param bool $abbreviated
     *
     * @return \Mtownsend\ReadTime\ReadTime
     */
    public function abbreviated($abbreviated = true)
    {
        $this->abbreviated = $abbreviated;

        return $this;
    }

    public function wordsCount(string $content): int
    {
        $str = trim($content);
        while (substr_count($str, '  ') > 0) {
            $str = str_replace('  ', ' ', $str);
        }

        return substr_count($str, ' ') + 1;
    }

    /**
     * Return the formatted read time string.
     */
    public function get(): string
    {
        $this->estimate();

        return $this->estimate['read_time'];
    }

    /**
     * Set ltr mode for the read time.
     *
     * @param  bool
     *
     * @return \Mtownsend\ReadTime\ReadTime
     */
    public function ltr(bool $ltr = true)
    {
        $this->ltr = $ltr;

        return $this;
    }

    /**
     * Omit seconds from being displayed in the read time result.
     *
     * @return \Mtownsend\ReadTime\ReadTime
     */
    public function omitSeconds(bool $omitSeconds = true)
    {
        $this->omitSeconds = $omitSeconds;

        return $this;
    }

    /**
     * Set the read time results to read from right to left.
     *
     * @return Mtownsend\ReadTime\ReadTime
     */
    public function rtl(bool $rtl = true)
    {
        $this->ltr = $rtl ? false : true;

        return $this;
    }

    /**
     * Return an array of the class data.
     */
    public function toArray(): array
    {
        $this->estimate();

        return array_merge($this->estimate, [
            'abbreviated' => (bool) $this->abbreviated,
            'left_to_right' => (bool) $this->ltr,
            'omit_seconds' => (bool) $this->omitSeconds,
            'words_in_content' => $this->wordsInContent,
            'words_per_minute' => (int) $this->wordsPerMinute,
        ]);
    }

    /**
     * Return a json string of the class data.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    /**
     * Set the average words read per minute.
     *
     * @return \Mtownsend\ReadTime\ReadTime
     */
    public function wpm(int $wordsPerMinute)
    {
        $this->wordsPerMinute = $wordsPerMinute;

        return $this;
    }

    /**
     * Calculate the reading time for minutes.
     */
    private function calculateMinutes(): int
    {
        $minutes = floor($this->wordsInContent / $this->wordsPerMinute);

        return (int) $minutes < 1 ? 1 : $minutes;
    }

    /**
     * Calculate the reading time for seconds.
     */
    private function calculateSeconds(): int
    {
        return (int) floor($this->wordsInContent % $this->wordsPerMinute / ($this->wordsPerMinute / 60));
    }

    /**
     * Strip html tags from content.
     *
     * @param string $content
     */
    private function cleanContent($content): string
    {
        return strip_tags($content);
    }

    /**
     * Set the estimate property.
     */
    private function estimate(): void
    {
        $this->estimate = [
            'minutes' => $this->calculateMinutes(),
            'seconds' => $this->omitSeconds ? 0 : $this->calculateSeconds(),
        ];
    }

    /**
     * Check if the given content is formatted appropriately.
     */
    private function invalidContent(mixed $content): bool
    {
        if (is_array($content) || is_string($content)) {
            return false;
        }

        return true;
    }

    /**
     * Parse the given content so it can be output as a read time.
     *
     * @param mixed $receivedContent String or array of content
     *
     * @return string
     */
    private function parseContent(mixed $receivedContent)
    {
        if ($this->invalidContent($receivedContent)) {
            throw new Exception('Content must be type of array or string');
        }

        if (is_array($receivedContent)) {
            $content = '';
            foreach ($receivedContent as $item) {
                if (is_array($item)) {
                    $item = $this->parseContent($item);
                }
                $content .= trim((string) $item);
            }
        } else {
            $content = $receivedContent;
        }

        return $this->cleanContent($content);
    }
}
