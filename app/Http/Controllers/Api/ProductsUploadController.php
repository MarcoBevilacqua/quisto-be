<?php

namespace App\Http\Controllers\Api;

use App\Actions\CsvFileStore;
use App\Actions\ImportProducts;
use App\Http\Controllers\Controller;
use App\Http\Requests\CsvUploadRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

;

class ProductsUploadController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function store(CsvUploadRequest $request, CsvFileStore $fileStoreHelper, ImportProducts $importProducts): Application|ResponseFactory|\Illuminate\Foundation\Application|Response
    {
        $request->validate($request->rules());
        $file = $request->file('products');

        // store file
        if(!$fileStoreHelper->exec($file)) {
            return response("Cannot Upload File");
        }

        // execute import action
        $batchId = $importProducts->exec($file);

        // return batch ID to monitor
        return response(['batchId' => $batchId]);
    }
}
