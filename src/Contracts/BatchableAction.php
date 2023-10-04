<?php

namespace Stepanenko3\LaravelApiSkeleton\Contracts;

use Illuminate\Bus\PendingBatch;

interface BatchableAction
{
    /**
     * Register callbacks on the pending batch.
     */
    public function withBatch(array $fields, PendingBatch $batch): void;
}
