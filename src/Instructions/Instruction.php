<?php

namespace Stepanenko3\LaravelApiSkeleton\Instructions;

use Illuminate\Support\Str;
use Stepanenko3\LaravelApiSkeleton\Concerns\Fieldable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Makeable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Metable;
use Stepanenko3\LaravelApiSkeleton\Concerns\Schemable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

class Instruction
{
    use Fieldable;
    use Makeable;
    use Metable;
    use Schemable;

    /**
     * The displayable name of the instruction.
     */
    public string $name;

    /**
     * Get the name of the instruction.
     */
    public function name(): string
    {
        return $this->name ?: Str::of(
            string: class_basename(
                class: get_class(
                    object: $this,
                ),
            ),
        )
            ->beforeLast(
                search: 'Instruction'
            )
            ->snake(
                delimiter: ' ',
            )
            ->title()
            ->toString();
    }

    /**
     * Get the URI key for the instruction.
     */
    public function uriKey(): string
    {
        return Str::slug(
            title: $this->name(),
            separator: '-',
            language: null,
        );
    }

    /**
     * Prepare the action for JSON serialization.
     */
    public function jsonSerialize(): array
    {
        $request = app()->make(
            abstract: Request::class
        );

        return [
            'name' => $this->name(),
            'uriKey' => $this->uriKey(),
            'fields' => $this->fields(
                request: $request,
            ),
            'meta' => $this->meta(),
        ];
    }

    /**
     * Perform the instruction on the given query.
     */
    public function handle(
        array $fields,
        Builder $query,
    ): void {
        // ...
    }
}
