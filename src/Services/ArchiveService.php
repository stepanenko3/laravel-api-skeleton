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

class ArchiveService
{
    protected string $archivePath = 'archives/';

    public function __construct(
        protected string $disk = 'local',
    ) {
        //
    }

    public function setDisk(
        string $disk,
    ): void {
        $this->disk = $disk;
    }

    public function archive(
        string $modelClass,
        Builder $query,
        int $chunkSize = 100,
        array $relations = [],
        ?string $customFileName = null
    ): void {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();
        $fileName = $customFileName ?? $this->archivePath . $tableName . '_' . now()->format('Y-m-d') . '.json';
        $archiveData = [];

        foreach ($this->chunkGenerator($query, $chunkSize) as $chunk) {
            $chunkData = [];
            $chunkIds = [];

            foreach ($chunk as $record) {
                $data = $record->toArray();

                foreach ($relations as $relation) {
                    $data[$relation] = $record->relationLoaded($relation)
                        ? $record->{$relation}->toArray()
                        : $record->{$relation}()->get()->toArray();
                }

                $chunkData[] = $data;
                $chunkIds[] = $record->getKey();
            }

            $archiveData = array_merge($archiveData, $chunkData);

            if (in_array(SoftDeletes::class, class_uses($modelInstance))) {
                $modelClass::query()
                    ->withoutGlobalScopes()
                    ->whereIn($modelInstance->getKeyName(), $chunkIds)
                    ->forceDelete();
            } else {
                $modelClass::query()
                    ->withoutGlobalScopes()
                    ->whereIn($modelInstance->getKeyName(), $chunkIds)
                    ->delete();
            }
        }

        if (!empty($archiveData)) {
            $this->writeJsonChunks($fileName, $archiveData);
        }
    }

    public function restore(
        string $modelClass,
        array $relations = [],
        ?Closure $conditions = null,
        ?string $date = null
    ): void {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();
        $files = $this->getArchiveFiles($tableName, $date);

        foreach ($files as $file) {
            $remainingRecords = [];

            foreach ($this->readJsonChunks($file, $conditions) as $record) {
                if ($conditions === null || $conditions($record)) {
                    $relationsData = $this->extractRelationsData($record, $relations);

                    foreach ($record as $key => $value) {
                        if (is_array($value)) {
                            $record[$key] = json_encode($value);
                        }
                    }

                    $id = DB::table($tableName)->insertGetId($record);

                    $this->insertRelationsData($modelInstance, $relationsData, $id);
                } else {
                    $remainingRecords[] = $record;
                }
            }

            if (!empty($remainingRecords)) {
                $this->writeJsonChunks($file, $remainingRecords);
            } else {
                Storage::disk($this->disk)->delete($file);
            }
        }
    }

    public function getArchivedRecords(
        string $modelClass,
        ?Closure $conditions = null
    ): array {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();
        $files = $this->getArchiveFiles($tableName);
        $results = [];

        foreach ($files as $file) {
            foreach ($this->readJsonChunks($file, $conditions) as $record) {
                $results[] = $record;
            }
        }

        return $results;
    }

    public function countArchivedRecords(
        string $modelClass,
        ?Closure $conditions = null
    ): int {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();
        $files = $this->getArchiveFiles($tableName);
        $count = 0;

        foreach ($files as $file) {
            foreach ($this->readJsonChunks($file, $conditions) as $record) {
                $count++;
            }
        }

        return $count;
    }

    public function listArchiveFiles(
        string $modelClass,
        ?string $date = null
    ): array {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();
        return $this->getArchiveFiles($tableName, $date);
    }

    public function deleteArchiveFiles(
        string $modelClass,
        ?string $date = null
    ): void {
        $modelInstance = new $modelClass();
        $tableName = $modelInstance->getTable();
        $files = $this->getArchiveFiles($tableName, $date);

        foreach ($files as $file) {
            Storage::disk($this->disk)->delete($file);
        }
    }

    protected function getArchiveFiles(
        string $tableName,
        ?string $date = null
    ): array {
        $allFiles = Storage::disk($this->disk)->allFiles($this->archivePath);
        $pattern = $this->archivePath . $tableName . ($date ? "_{$date}" : '');

        return array_filter($allFiles, fn($file) => Str::startsWith($file, $pattern) && Str::endsWith($file, '.json'));
    }

    protected function writeJsonChunks(
        string $fileName,
        array $data,
        int $chunkSize = 1000
    ): void {
        if (!Storage::disk($this->disk)->exists($this->archivePath)) {
            Storage::disk($this->disk)->makeDirectory($this->archivePath);
        }

        $filePath = Storage::disk($this->disk)->path($fileName);
        $handle = fopen($filePath, 'w');
        $chunks = array_chunk($data, $chunkSize);

        foreach ($chunks as $chunk) {
            fwrite($handle, json_encode($chunk));
        }

        fclose($handle);
    }

    protected function readJsonChunks(
        string $file,
        ?Closure $conditions = null
    ): Generator {
        $filePath = Storage::disk($this->disk)->path($file);

        foreach (JsonParser::parse($filePath) as $record) {
            $record = (array) $record;
            if ($conditions === null || $conditions($record)) {
                yield $record;
            }
        }
    }

    protected function chunkGenerator(
        Builder $query,
        int $chunkSize
    ): Generator {
        $offset = 0;
        do {
            $chunk = $query->take($chunkSize)->get();

            if ($chunk->isEmpty()) {
                break;
            }

            yield $chunk;

            $offset += $chunkSize;
        } while ($chunk->count() === $chunkSize);
    }

    protected function extractRelationsData(
        array &$record,
        array $relations
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
        int $id
    ): void {
        foreach ($relationsData as $relation => $data) {
            foreach ($data as &$relatedRecord) {
                $relationKey = Str::snake(class_basename($modelInstance)) . '_id';
                $relatedRecord[$relationKey] = $id;
            }

            $relationTable = $modelInstance->{$relation}()->getRelated()->getTable();
            DB::table($relationTable)->insert($data);
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
//     date: $date
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
//     date: '2024-08-08'
// );

// Удаление архивных файлов
// $service->deleteArchiveFiles(
//     modelClass: BlogPost::class,
//     date: '2024-08-08'
// );

// Установка диска для хранения на Amazon S3
// $service->setDisk('s3');
