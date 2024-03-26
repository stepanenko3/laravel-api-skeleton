<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

trait HasGeneratedCode
{
    public function scopeFindByCode(
        Builder $query,
        string $code
    ): Model {
        return $query
            ->where(
                column: $this->codeField(),
                operator: '=',
                value: $code,
            )
            ->firstOrFail();
    }

    public function refreshCode(): void
    {
        $this->{$this->codeField()} = $this->generateCode();
    }

    public function generateCode(): string
    {
        $code = Str::random(10);

        if ($this
            ->where(
                column: $this->codeField(),
                operator: '=',
                value: $code,
            )
            ->exists()
        ) {
            return $this->generateCode();
        }

        return $code;
    }

    protected static function bootHasGeneratedCode(): void
    {
        static::creating(
            function (Model $model): void {
                $model->refreshCode();
            },
        );
    }

    protected function codeField(): string
    {
        return 'code';
    }
}
