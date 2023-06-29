<?php

namespace App\Actions;

use App\Helpers\CsvRowImportHelper;
use Illuminate\Bus\Batch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;

class ImportProducts
{

    /**
     * The batch name
     */
    public const CSV_IMPORT_BATCH_NAME = 'csv-product-import';

    /**
     * the number of rows a job should process
     */
    private const CSV_IMPORT_CHUNK = 5000;

    /**
     * the column headers
     * @var array|string[]
     */
    private array $header = ["id","name","price"];

    /**
     * @var int $rowCount
     */
    private int $rowCount = 0;

    /**
     * the jobs to put in batch
     * @var array
     */
    private array $jobs = [];

    /**
     * @param UploadedFile $uploadedFile
     * @return string
     * @throws \Throwable
     */
    public function exec(UploadedFile $uploadedFile) {

        $importHelper = new CsvRowImportHelper();

        // read file content and save data
        $productFile = fopen($uploadedFile->path(), 'r');

        // remove first line
        fgetcsv($productFile, 1000);

        // row values to process
        $rows = [];

        while ($row = fgetcsv($productFile)) {

            $rows[] = array_combine($this->header, $row);

            if($this->rowCount === self::CSV_IMPORT_CHUNK) {
                $this->jobs[] = $importHelper->getDataFromFile($rows);
                $this->rowCount = 0;
                $rows = [];
            }

            //increment row count
            $this->rowCount++;
        }

        // put the last job in the batch
        if(count($rows) > 0) {
            $this->jobs[] = $importHelper->getDataFromFile($rows);
        }

        fclose($productFile);

        return $this->dispatchJobBatch($this->jobs);
    }

    /**
     * @throws \Throwable
     */
    private function dispatchJobBatch(array $chunks): string
    {
        $batch = Bus::batch($chunks)->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, \Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->name(self::CSV_IMPORT_BATCH_NAME)
            // Allow batch to continue executing
            ->allowFailures()
            ->dispatch();

        return $batch->id;
    }

}
