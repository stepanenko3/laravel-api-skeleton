<?php

namespace Stepanenko3\LaravelApiSkeleton\Database\Migrations;

use Illuminate\Database\Migrations\Migration as MigrationsMigration;
use Illuminate\Support\Facades\DB;
use Stepanenko3\LaravelApiSkeleton\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class Migration extends MigrationsMigration
{
    public function schema(): Builder
    {
        $schema = DB::connection()->getSchemaBuilder();
        $schema->blueprintResolver(fn ($table, $callback) => new Blueprint($table, $callback));

        return $schema;
    }
}
