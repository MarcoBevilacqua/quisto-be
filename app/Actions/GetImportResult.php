<?php

namespace App\Actions;

use App\Models\ImportResult;

class GetImportResult
{
    public function monitor(string $batchId): array
    {
        $importResult = ImportResult::where('batch_id', '=', $batchId)
            ->select(['updated', 'created'])
            ->first();

        if(!$importResult) {
            return [];
        }

        return $importResult->toArray();
    }
}
