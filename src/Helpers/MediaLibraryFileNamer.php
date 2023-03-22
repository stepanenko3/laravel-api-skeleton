<?php

namespace Stepanenko3\LaravelLogicContainers\Helpers;

use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\FileNamer;

class MediaLibraryFileNamer extends FileNamer
{
    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        $strippedFileName = mb_pathinfo($fileName, PATHINFO_FILENAME);

        return substr(base_convert(md5($strippedFileName), 16, 32), 0, 12) . '-' . $conversion->getName();
    }

    public function responsiveFileName(string $fileName): string
    {
        return mb_pathinfo($fileName, PATHINFO_FILENAME);
    }
}
