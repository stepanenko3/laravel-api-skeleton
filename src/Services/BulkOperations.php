<?php

namespace Stepanenko3\LaravelApiSkeleton\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkOperations
{
    /** Perform bulk create operation. */
    public static function bulkCreate(
        string $model,
        array $data,
    ): void {
        if (empty($data)) {
            Log::warning('No data provided for bulkCreate operation.');

            return;
        }

        DB::transaction(fn () => $model::insert($data));

        Log::info('Bulk create operation completed successfully.');
    }

    /** Perform bulk update operation. */
    public static function bulkUpdate(
        string $model,
        array $data,
        string $key = 'id',
    ): void {
        if (empty($data)) {
            Log::warning('No data provided for bulkUpdate operation.');

            return;
        }

        DB::transaction(function () use ($model, $data, $key): void {
            foreach ($data as $item) {
                if (isset($item[$key])) {
                    $model::query()
                        ->where($key, $item[$key])
                        ->update($item);
                } else {
                    Log::error("Key {$key} not found in item: " . json_encode($item));
                }
            }
        });

        Log::info('Bulk update operation completed successfully.');
    }

    /** Perform bulk delete operation. */
    public static function bulkDelete(
        string $model,
        array $ids,
        string $key = 'id',
    ): void {
        if (empty($ids)) {
            Log::warning('No IDs provided for bulkDelete operation.');

            return;
        }

        DB::transaction(
            fn () => $model::query()
                ->whereIn($key, $ids)
                ->delete(),
        );

        Log::info('Bulk delete operation completed successfully.');
    }
}
