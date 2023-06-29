<?php

namespace App\Actions;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CsvFileStore
{
    /**
     * the path to upload the file
     */
    const CSV_UPLOAD_PATH = "/";

    public function exec(UploadedFile $uploadedFile): bool
    {
        try {
            Storage::disk('local')->put(self::CSV_UPLOAD_PATH, $uploadedFile);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }

        return true;
    }
}
