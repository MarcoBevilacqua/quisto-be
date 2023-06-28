<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportProductFromCsv;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use function response;

class ProductsUploadController extends Controller
{

    private const CSV_IMPORT_CHUNK = 10;

    /**
     * @throws \Throwable
     */
    public function store(Request $request): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Illuminate\Http\Response
    {
        $file = $request->file('products');

        try {
            Storage::disk('local')
                ->put('/', $file);
        } catch (\Exception $exception) {
            return response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        // read file content and save data
        $file = fopen($file->path(), 'r');

        $header = ["id","name","price"];

        $rows = [];
        $jobs = [];
        $rowCount = 0;
        while ($row = fgetcsv($file)) {
            $rowCount++;
            $rows[] = array_combine($header, $row);
            if($rowCount === self::CSV_IMPORT_CHUNK) {
                $jobs[] = new ImportProductFromCsv($rows);
                $rowCount = 0;
                $rows = [];
            }
        }

        // put the last job in the batch
        if(count($rows) > 0) {
            $jobs[] = new ImportProductFromCsv($rows);
        }

        fclose($file);

        $batchId = $this->dispatchJobBatch($jobs);

        return response(['batchId' => $batchId]);
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
        })->name('csv-product-import')
            // Allow batch to continue executing
            ->allowFailures()
            ->dispatch();

        return $batch->id;
    }
}
