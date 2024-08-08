<?php

namespace Stepanenko3\LaravelApiSkeleton\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Cerbero\JsonParser\JsonParser;
use Closure;
use Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ArchiveService
{
    public function __construct(
        protected string $disk = 'local',
        protected string $archivePath = 'archives/',
    ) {
        $this->setArchivePath(
            archivePath: $this->archivePath,
        );
    }

    public function setDisk(
        string $disk,
    ): void {
        $this->disk = $disk;
    }

    public function setArchivePath(
        string $archivePath,
    ): void {
        if (!Str::endsWith($archivePath, '/')) {
            $archivePath .= '/';
        }
        $this->archivePath = $archivePath;
    }

    public function archive(
        string $modelClass,
        Builder $query,
        int $chunkSize = 1000,
        array $relations = [],
    ): int {
        [
            $modelInstance,
            $tableName,
        ] = $this->prepareModel(
            modelClass: $modelClass,
        );

        $total = 0;
        $archiveData = [];

        foreach ($this->chunkGenerator($query, $chunkSize) as $chunk) {
            $chunkData = [];
            $chunkIds = [];

            foreach ($chunk as $record) {
                $data = $record->getOriginal();

                foreach ($relations as $relation) {
                    $data[$relation] = $record->relationLoaded($relation)
                        ? $record->{$relation}->toArray()
                        : $record->{$relation}()->get()->toArray();
                }

                $chunkData[] = $data;
                $chunkIds[] = $record->getKey();
            }

            $archiveData = array_merge(
                $archiveData,
                $chunkData,
            );

            // Here you should avoid deleting records immediately
            $total += $this->deleteRecords(
                modelInstance: $modelInstance,
                chunkIds: $chunkIds,
            );
        }

        if (!empty($archiveData)) {
            $fileName = $this->archivePath . $tableName . '_' . now()->format('Y-m-d') . '.json';

            $this->writeJsonChunks(
                fileName: $fileName,
                data: $archiveData,
            );
        }

        return $total;
    }

    public function restore(
        string $modelClass,
        array $relations = [],
        ?Closure $conditions = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): void {
        $filesToDelete = [];

        $this->processArchiveFiles(
            modelClass: $modelClass,
            conditions: $conditions,
            startDate: $startDate,
            endDate: $endDate,
            callback: function ($record, $file, $modelInstance, $tableName) use ($relations, &$filesToDelete): void {
                $relationsData = $this->extractRelationsData(
                    record: $record,
                    relations: $relations,
                );

                foreach ($record as $key => $value) {
                    if (is_array($value)) {
                        $record[$key] = json_encode($value);
                    }
                }

                // Update the `updated_at` field to `now()` if it exists
                if (array_key_exists('updated_at', $record)) {
                    $record['updated_at'] = now();
                }

                $id = DB::table($tableName)
                    ->insertGetId($record);

                $this->insertRelationsData(
                    modelInstance: $modelInstance,
                    relationsData: $relationsData,
                    id: $id,
                );

                // Collect the file to delete later
                $filesToDelete[$file][] = $record;
            }
        );

        $keyName = (new $modelClass())->getKeyName();

        // Delete the files after restoration
        foreach ($filesToDelete as $file => $records) {
            $this->deleteFileRecords(
                keyName: $keyName,
                file: $file,
                records: $records,
            );
        }
    }

    public function getArchivedRecords(
        string $modelClass,
        ?Closure $conditions = null,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $instantiateModels = false,
        array $relations = [],
    ): array {
        $results = [];

        $this->processArchiveFiles(
            modelClass: $modelClass,
            conditions: $conditions,
            startDate: $startDate,
            endDate: $endDate,
            callback: function ($record) use (&$results, $modelClass, $instantiateModels, $relations): void {
                if ($instantiateModels) {
                    $results[] = $this->makeModelInstanceWithRelations(
                        modelClass: $modelClass,
                        record: $record,
                        relations: $relations,
                    );
                } else {
                    $results[] = $record;
                }
            },
        );

        return $results;
    }

    public function countArchivedRecords(
        string $modelClass,
        ?Closure $conditions = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): int {
        $count = 0;

        $this->processArchiveFiles(
            modelClass: $modelClass,
            conditions: $conditions,
            startDate: $startDate,
            endDate: $endDate,
            callback: function ($record) use (&$count): void {
                $count++;
            },
        );

        return $count;
    }

    public function listArchiveFiles(
        string $modelClass,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        [$modelInstance, $tableName] = $this->prepareModel(
            modelClass: $modelClass,
        );

        return $this->getArchiveFiles(
            tableName: $tableName,
            startDate: $startDate,
            endDate: $endDate,
        );
    }

    public function deleteArchiveFiles(
        string $modelClass,
        ?string $startDate = null,
        ?string $endDate = null
    ): void {
        $this->processArchiveFiles(
            modelClass: $modelClass,
            conditions: null,
            startDate: $startDate,
            endDate: $endDate,
            callback: function ($record, $file): void {
                Storage::disk($this->disk)
                    ->delete($file);
            },
        );
    }

    protected function deleteFileRecords(
        string $keyName,
        string $file,
        array $records,
    ): void {
        $filePath = Storage::disk($this->disk)
            ->path($file);

        $fileContents = file_get_contents($filePath);
        $archiveData = json_decode($fileContents, true);

        $keys = array_column($records, $keyName);

        // Remove the restored records from the archive data
        $updatedData = array_filter(
            $archiveData,
            fn ($archiveRecord) => !in_array($archiveRecord[$keyName], $keys),
        );

        // If the updated data is empty, delete the file
        if (empty($updatedData)) {
            Storage::disk($this->disk)
                ->delete($file);
        } else {
            // Otherwise, update the file with the remaining data
            file_put_contents(
                $filePath,
                json_encode($updatedData),
            );
        }
    }

    protected function makeModelInstanceWithRelations(
        string $modelClass,
        array $record,
        array $relations = [],
    ): Model {
        /** @var Model */
        $modelInstance = new $modelClass();

        $relationsData = $this->extractRelationsData(
            record: $record,
            relations: $relations,
        );

        $modelInstance->forceFill(
            attributes: $record,
        );

        foreach ($relationsData as $relation => $data) {
            $relationModelClass = get_class(
                object: $modelInstance
                    ->{$relation}()
                    ->getRelated(),
            );

            $relatedModels = array_map(
                array: $data,
                callback: function ($relatedRecord) use ($relationModelClass) {
                    return $this->makeModelInstance(
                        modelClass: $relationModelClass,
                        record: $relatedRecord,
                    );
                },
            );

            $modelInstance->setRelation(
                relation: $relation,
                value: collect($relatedModels),
            );

            $attributes = $modelInstance->attributesToArray();

            Arr::forget(
                array: $attributes,
                keys: $relation,
            );

            $modelInstance->setRawAttributes(
                attributes: $attributes,
                sync: true,
            );
        }

        return $modelInstance;
    }

    protected function makeModelInstance(
        string $modelClass,
        array $record
    ): Model {
        /** @var Model */
        $modelInstance = new $modelClass();

        $modelInstance->forceFill(
            attributes: $record,
        );

        return $modelInstance;
    }

    protected function prepareModel(
        string $modelClass,
    ): array {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();

        return [
            $modelInstance,
            $tableName,
        ];
    }

    protected function deleteRecords(
        Model $modelInstance,
        array $chunkIds,
    ): int {
        $query = ($modelInstance::class)::query()
            ->withoutGlobalScopes()
            ->whereIn(
                column: $modelInstance->getKeyName(),
                values: $chunkIds,
            );

        $hasSoftDeletes = in_array(
            SoftDeletes::class,
            class_uses_recursive($modelInstance),
        );

        if ($hasSoftDeletes) {
            return $query->forceDelete();
        }

        return $query->delete();
    }

    protected function getArchiveFiles(
        string $tableName,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        $allFiles = Storage::disk($this->disk)
            ->allFiles($this->archivePath);

        $pattern = $this->archivePath . $tableName;

        return array_filter(
            $allFiles,
            function ($file) use ($pattern, $startDate, $endDate) {
                if (!Str::startsWith($file, $pattern) || !Str::endsWith($file, '.json')) {
                    return false;
                }

                if ($startDate || $endDate) {
                    $datePart = str_replace([$pattern . '_', '.json'], '', $file);

                    $fileDate = Carbon::createFromFormat('Y-m-d', $datePart);

                    if ($startDate && $fileDate->lt($startDate)) {
                        return false;
                    }

                    if ($endDate && $fileDate->gt($endDate)) {
                        return false;
                    }
                }

                return true;
            }
        );
    }

    protected function processArchiveFiles(
        string $modelClass,
        ?Closure $conditions,
        ?string $startDate,
        ?string $endDate,
        Closure $callback,
    ): void {
        [$modelInstance, $tableName] = $this->prepareModel(
            modelClass: $modelClass,
        );

        $files = $this->getArchiveFiles(
            tableName: $tableName,
            startDate: $startDate,
            endDate: $endDate,
        );

        foreach ($files as $file) {
            foreach ($this->readJsonChunks($file, $conditions) as $record) {
                $callback($record, $file, $modelInstance, $tableName);
            }
        }
    }

    protected function writeJsonChunks(
        string $fileName,
        array $data,
        int $chunkSize = 1000,
    ): void {
        if (!Storage::disk($this->disk)->exists($this->archivePath)) {
            Storage::disk($this->disk)->makeDirectory($this->archivePath);
        }

        $filePath = Storage::disk($this->disk)
            ->path($fileName);

        $handle = fopen($filePath, 'w');
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            fwrite($handle, json_encode($chunk));
        }

        fclose($handle);
    }

    protected function readJsonChunks(
        string $file,
        ?Closure $conditions = null,
    ): Generator {
        $filePath = Storage::disk($this->disk)
            ->path($file);

        foreach (JsonParser::parse($filePath) as $record) {
            $record = (array) $record;

            if ($conditions === null || $conditions($record)) {
                yield $record;
            }
        }
    }

    protected function chunkGenerator(
        Builder $query,
        int $chunkSize,
    ): Generator {
        $offset = 0;
        do {
            $chunk = $query
                ->take($chunkSize)
                ->get();

            if ($chunk->isEmpty()) {
                break;
            }

            yield $chunk;

            $offset += $chunkSize;
        } while ($chunk->count() === $chunkSize);
    }

    protected function extractRelationsData(
        array &$record,
        array $relations,
    ): array {
        $relationsData = [];

        foreach ($relations as $relation) {
            if (isset($record[$relation])) {
                $relationsData[$relation] = $record[$relation];

                unset($record[$relation]);
            }
        }

        return $relationsData;
    }

    protected function insertRelationsData(
        Model $modelInstance,
        array $relationsData,
        int $id,
    ): void {
        foreach ($relationsData as $relation => $data) {
            foreach ($data as &$relatedRecord) {
                $relationKey = Str::camel(class_basename($modelInstance)) . '_id';

                $relatedRecord[$relationKey] = $id;

                foreach ($relatedRecord as $key => $value) {
                    if (is_array($value)) {
                        $relatedRecord[$key] = json_encode($value);
                    }
                }
            }

            $relationTable = $modelInstance
                ->{$relation}()
                ->getRelated()
                ->getTable();

            DB::table($relationTable)
                ->insert($data);
        }
    }
}

// $service = new ArchiveService();

// Archive BlogPost records
// $service->archive(
//     modelClass: BlogPost::class,
//     query: BlogPost::query()
//         ->withoutGlobalScopes(),
//     chunkSize: 500,
//     relations: [],
// );

// Restore all archived BlogPost records
// $service->restore(
//     modelClass: BlogPost::class,
//     relations: ['comments', 'tags'],
// );

// Restore archived BlogPost records by a specific user
// $userId = 10;
// $service->restore(
//     modelClass: BlogPost::class,
//     relations: ['comments', 'tags'],
//     conditions: function($record) use ($userId) {
//         return $record['user_id'] === $userId;
//     }
// );

// Restore archived BlogPost records from a specific date
// $date = '2024-08-08';

// $service->restore(
//     modelClass: BlogPost::class,
//     relations: ['comments', 'tags'],
//     startDate: $date
// );

// Retrieve paginated archived BlogPost records
// $archivedRecords = $service->getArchivedRecords(
//     modelClass: BlogPost::class,
//     conditions: function ($record) {
//         return $record['user_id'] === 10;
//     }
// );

// Display paginated archived records
// foreach ($archivedRecords as $pageIndex => $pageRecords) {
//     echo "Page " . ($pageIndex + 1) . ":\n";
//     foreach ($pageRecords as $record) {
//         print_r($record);
//     }
//     echo "\n";
// }

// Архивирование с пользовательским именем файла
// $service->archive(
//     modelClass: BlogPost::class,
//     query: BlogPost::query()->withoutGlobalScopes(),
//     chunkSize: 500,
//     relations: [],
//     customFileName: 'custom_archive_name.json'
// );

// Подсчет количества архивированных записей
// $count = $service->countArchivedRecords(
//     modelClass: BlogPost::class,
//     conditions: function ($record) {
//         return $record['user_id'] === 10;
//     }
// );

// Получение списка архивных файлов
// $archiveFiles = $service->listArchiveFiles(
//     modelClass: BlogPost::class,
//     startDate: '2024-08-08'
// );

// Удаление архивных файлов
// $service->deleteArchiveFiles(
//     modelClass: BlogPost::class,
//     startDate: '2024-08-08'
// );

// Установка диска для хранения на Amazon S3
// $service->setDisk('s3');
