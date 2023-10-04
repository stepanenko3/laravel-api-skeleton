<?php

namespace Stepanenko3\LaravelApiSkeleton\Concerns;

use Stepanenko3\LaravelApiSkeleton\Database\Eloquent\Model;
use Stepanenko3\LaravelApiSkeleton\Http\Requests\Request;

trait PerformsModelOperations
{
    /**
     * Build a "delete" query for the given resource.
     */
    public function performDelete(
        Request $request,
        Model $model,
    ): void {
        $model->delete();
    }

    /**
     * Build a "restore" query for the given resource.
     */
    public function performRestore(
        Request $request,
        Model $model,
    ): void {
        $model->restore();
    }

    /**
     * Build a "forceDelete" query for the given resource.
     */
    public function performForceDelete(
        Request $request,
        Model $model,
    ): void {
        $model->forceDelete();
    }
}
