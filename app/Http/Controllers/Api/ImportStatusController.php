<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class ImportStatusController extends Controller
{
    //invokable
    public function __invoke(string $batchId)
    {
        $batch = Bus::findBatch($batchId);

        $failedJobs = [];

        if(count($batch->failedJobIds) > 0) {
            $failedJobs = DB::table('failed_jobs')
                ->select(['id', 'uuid', 'payload', 'exception'])
                ->whereIn('uuid', $batch->failedJobIds)
                ->get()
                ->filter(function ($item) use ($batchId) {
                    $commandPayload = unserialize(json_decode($item->payload)->data->command);
                    return $batchId === $commandPayload->batchId;
                })
                ->toArray();

        }

        return response([
            'name'      => $batch->name,
            'progress'  => $batch->progress(),
            'pending'   => $batch->pendingJobs,
            'failed'    => $batch->failedJobs,
            'finished'  => $batch->finished(),
            'errors'    => $failedJobs
        ]);
    }
}
