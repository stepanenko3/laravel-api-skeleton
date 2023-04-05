<?php

namespace Stepanenko3\LaravelApiSkeleton\Helpers;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class MediaLibraryFileNamer extends FileNamer
{
    public function originalFileName(string $fileName): string
    {
        $strippedFileName = mb_pathinfo($fileName, PATHINFO_FILENAME);

        return substr(base_convert(md5($strippedFileName), 16, 32), 0, 12);
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        return $this->originalFileName($fileName) . '-' . $conversion->getName();
    }

    public function responsiveFileName(string $fileName): string
    {
        return mb_pathinfo($fileName, PATHINFO_FILENAME);
    }
}
