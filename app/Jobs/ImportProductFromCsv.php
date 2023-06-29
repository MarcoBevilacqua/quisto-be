<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportProductFromCsv implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $rows;

    private string $name;
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
            $row->import();
        }
    }
}
