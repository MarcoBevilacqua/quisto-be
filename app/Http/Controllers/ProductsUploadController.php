<?php

namespace App\Http\Controllers;

use App\Jobs\ImportProductFromCsv;
use Illuminate\Bus\Batch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ProductsUploadController extends Controller
{

    private const CSV_IMPORT_CHUNK = 3;

    /**
     * @throws \Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $file = $request->file('products');
        $fileSaved = Storage::disk('csv')
            ->put('/', $file);

        if(! $fileSaved) {
            return redirect()->back(Response::HTTP_BAD_REQUEST)
                ->with('error', "Error during file save");
        }

        // read file content and save data
        $file = fopen($file->path(), 'r');

        $header = fgetcsv($file);

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

        $this->dispatchJobBatch($jobs);

        return redirect()->back();
    }

    /**
     * @throws \Throwable
     */
    private function dispatchJobBatch(array $chunks): string
    {
        $batch = Bus::batch($chunks)->then(function (Batch $batch) {
            // All jobs completed successfully...
            echo $batch->id;
        })->catch(function (Batch $batch, \Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->name('csv-product-import')->dispatch();

        return $batch->id;
    }
}
