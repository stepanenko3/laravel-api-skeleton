<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers;

use Closure;
use Illuminate\Support\Str;

class ReadContent
{
    private array $estimate = [];

    private array $headings = [];

    private array $tags = [];

    public function __construct(
        private string $content,
        private readonly Closure $routeMaker,
    ) {
        $this->applyHeadings();
        $this->applyTags();
    }

    public function __invoke()
    {
        return $this->toArray();
    }

    /**
     * Return an array of the class data.
     */
    public function toArray(): array
    {
        $this->estimate();

        return $this->estimate;
    }

    private function applyHeadings(): array
    {
        preg_match_all('#<h([1-6])>(.+?)</h\1>#is', $this->content, $matches, PREG_SET_ORDER, 0);

        $index = 1;

        foreach ($matches as $match) {
            $text = strip_tags($match[2] ?? '');

            if (!$text) {
                continue;
            }

            $id = Str::slug('heading-' . $index);
            $html = mb_strpos($match[0], 'id="') === false
                ? str_ireplace('<h' . $match[1], '<h' . $match[1] . ' id="' . $id . '"', $match[0])
                : $match[0];

            $this->content = str_ireplace(
                $match[0],
                $html,
                $this->content,
            );

            $this->headings[] = [
                'text' => html_entity_decode($text),
                'id' => $id,
            ];

            $index++;
        }

        return $this->headings;
    }

    private function applyTags(): array
    {
        preg_match_all('/#(\w+)/', $this->content, $tags);

        $link = str_ireplace('_1_', '$1', (string) ($this->routeMaker)('_1_'));

        $pat = ['/#(\w+)/', '/@(\w+)/'];
        $rep = [
            '<a href="' . $link . '">#$1</a>',
            '<a href="' . $link . '">@$1</a>',
        ];

        $this->content = preg_replace($pat, $rep, $this->content);

        return $this->tags = ecollect($tags[1])
            ->unique()
            ->filter()
            ->values()
            ->sortByDesc(fn ($tag) => mb_strlen((string) $tag))
            ->map(fn ($tag) => [
                'text' => $tag,
                'link' => str_ireplace('$1', (string) $tag, $link),
            ])
            ->toArray();
    }

    /**
     * Set the estimate property.
     */
    private function estimate(): void
    {
        $this->estimate = [
            'tags' => $this->tags,
            'headings' => $this->headings,
            'content' => make_body($this->content),
            'read_time' => (new ReadTime($this->content))
                ->toArray(),
        ];
    }
}
