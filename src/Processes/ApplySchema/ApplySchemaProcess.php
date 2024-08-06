<?php

namespace Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema;

use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\ApplyCountRelations;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\ApplyFields;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\ApplyRelations;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\ApplyScopes;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\ApplySorts;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\Authorize;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\MutateDTO;
use Stepanenko3\LaravelApiSkeleton\Processes\ApplySchema\Tasks\PerformQuery;
use Stepanenko3\LaravelApiSkeleton\Processes\Process;

class ApplySchemaProcess extends Process
{
    public array $tasks = [
        Authorize::class,
        MutateDTO::class,
        ApplyFields::class,
        ApplyRelations::class,
        ApplyCountRelations::class,
        ApplyScopes::class,
        ApplySorts::class,
        PerformQuery::class,
    ];
}
