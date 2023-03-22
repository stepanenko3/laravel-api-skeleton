<?php

namespace Stepanenko3\LaravelLogicContainers\Database\Schema;

use Illuminate\Database\Schema\Blueprint as IlluminateBlueprint;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Str;

class Blueprint extends IlluminateBlueprint
{
    public function index(
        $columns,
        $name = null,
        $algorithm = null,
    ) {
        return $this->indexCommand('index', $columns, $name, $algorithm);
    }

    public function primary($columns, $name = null, $algorithm = null)
    {
        return $this->indexCommand('primary', $columns, $name, $algorithm);
    }

    public function unique($columns, $name = null, $algorithm = null)
    {
        return $this->indexCommand('unique', $columns, $name, $algorithm);
    }

    public function foreign($columns, $name = null, $algorithm = null)
    {
        $command = new ForeignKeyDefinition(
            $this->indexCommand('foreign', $columns, $name)->getAttributes()
        );

        $this->commands[count($this->commands) - 1] = $command;

        return $command;
    }

    public function generateIndexName(array $columns, string $name = 'index'): string
    {
        $table_name = $this->getFirstLetters($this->table);

        $columns_name = implode(
            '',
            array_map(fn ($column) => $this->getFirstLetters($column), $columns),
        );

        $index_name = Str::random(10);

        return "{$table_name}_{$columns_name}_{$index_name}_{$name}";
    }

    protected function indexCommand($type, $columns, $index, $algorithm = null)
    {
        if ($index === null) {
            $index = $this->generateIndexName((array) $columns, $type);
        }

        return parent::indexCommand($type, $columns, $index, $algorithm);
    }

    private function getFirstLetters(string $string)
    {
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
