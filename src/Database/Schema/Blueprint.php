<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class Blueprint extends IlluminateBlueprint
{
    public function index(
        $columns,
        $name = null,
        $algorithm = null,
    ) {
        return $this->indexCommand(
            type: 'index',
            columns: $columns,
            index: $name,
            algorithm: $algorithm,
        );
    }

    public function primary(
        $columns,
        $name = null,
        $algorithm = null,
    ) {
        return $this->indexCommand(
            type: 'primary',
            columns: $columns,
            index: $name,
            algorithm: $algorithm,
        );
    }

    public function unique(
        $columns,
        $name = null,
        $algorithm = null,
    ) {
        return $this->indexCommand(
            type: 'unique',
            columns: $columns,
            index: $name,
            algorithm: $algorithm,
        );
    }

    public function foreign(
        $columns,
        $name = null,
        $algorithm = null,
    ) {
        $command = new ForeignKeyDefinition(
            $this
                ->indexCommand(
                    type: 'foreign',
                    columns: $columns,
                    index: $name,
                )
                ->getAttributes()
        );

        $this->commands[count($this->commands) - 1] = $command;

        return $command;
    }

    public function generateIndexName(
        array $columns,
        string $name = 'index',
    ): string {
        $table_name = $this->getFirstLetters(
            string: $this->table,
        );

        $columns_name = implode(
            '',
            array_map(fn ($column) => $this->getFirstLetters($column), $columns),
        );

        $index_name = Str::random(10);

        return "{$table_name}_{$columns_name}_{$index_name}_{$name}";
    }

    protected function indexCommand(
        $type,
        $columns,
        $index,
        $algorithm = null,
    ): Fluent {
        if ($index === null) {
            $index = $this->generateIndexName(
                columns: (array) $columns,
                name: $type,
            );
        }

        return parent::indexCommand(
            type: $type,
            columns: $columns,
            index: $index,
            algorithm: $algorithm,
        );
    }

    private function getFirstLetters(
        string $string,
    ): string {
        return implode(
            '',
            array_map(
                fn ($word) => $word[0],
                array_filter(
                    preg_split(
                        '/[\\s,_-]+/',
                        trim($string, '_'),
                    ),
                ),
            ),
        );
    }
}
