<?php

namespace App\Http\Controllers\Api;

use App\Actions\GetBatchStatus;
use App\Actions\GetImportResult;
use App\Http\Controllers\Controller;

class ImportStatusController extends Controller
{
    //invokable
    public function __invoke(string $batchId, GetBatchStatus $batchStatus, GetImportResult $importResult)
    {
        return response(
            array_merge(
                $batchStatus->monitor($batchId),
                $importResult->monitor($batchId)
            )
        );
    }
}
