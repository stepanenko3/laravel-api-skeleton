<?php

namespace Stepanenko3\LaravelApiSkeleton\Traits;

use Stepanenko3\LaravelApiSkeleton\Services\ArchiveService;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Events\ModelsPruned;

trait ArchivedPrunable
{
    use Prunable;

    /**
     * Prune all prunable models in the database.
     *
     * @return int
     */
    public static function pruneAll(
        int $chunkSize = 1000,
    ) {
        $archiveService = new ArchiveService();
        $total = 0;

        $instance = new static();

        $query = $instance
            ->prunable()
            ->when(
                value: in_array(
                    SoftDeletes::class,
                    class_uses_recursive($instance::class),
                ),
                callback: function ($query): void {
                    $query->withTrashed();
                }
            );

        $relations = array_keys(
            $query->getEagerLoads(),
        );

        $total = $archiveService->archive(
            modelClass: static::class,
            query: $query,
            chunkSize: $chunkSize,
            relations: $relations,
        );

        event(new ModelsPruned(static::class, $total));

        return $total;
    }

    public function prunableRelations(): array
    {
        return [];
    }
}
