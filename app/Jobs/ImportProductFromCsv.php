<?php

namespace App\Jobs;

use App\Models\ImportResult;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProductFromCsv implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * the rows to process
     * @var array
     */
    private array $rows;

    /**
     * the job name
     * @var string
     */
    private string $name;

    /**
     * the import status
     * @var array|int[]
     */
    private array $results = ['updated' => 0, 'created' => 0];
    /**
     * Create a new job instance.
     */
    public function __construct(array $rows)
    {
        $this->name = 'csv-product-import';
        $this->rows = $rows;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        foreach ($this->rows as $row) {
            if($row->import()) {
                $this->results['created']++;
            } else {
                $this->results['updated']++;
            }
        }

        $importResult = ImportResult::where('batch_id', '=', $this->batchId)->first();

        if(!$importResult) {
            ImportResult::create([
                "batch_id" => $this->batchId,
                "created" => $this->results['created'],
                "updated" => $this->results['updated']
                ]
            );
        } else {
            //update import result
            $importResult->update([
                "created" => $this->results['created'] + $importResult->created,
                "updated" => $this->results['updated'] + $importResult->updated]
            );
        }


    }
}
