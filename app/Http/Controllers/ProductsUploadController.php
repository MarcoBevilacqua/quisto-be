<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProductsUploadController extends Controller
{
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $file = $request->file('products');
        $fileSaved = Storage::disk('csv')
            ->put('/', $file);

        if(! $fileSaved) {
            return redirect()->back(RedirectResponse::HTTP_BAD_REQUEST)
                ->with('error', "Error during file save");
        }


        // read file content and save data
        $file = fopen($file->path(), 'r');

        $header = fgetcsv($file);

        while ($row = fgetcsv($file)) {
            $this->handleRow(array_combine($header, $row));
        }

        fclose($file);


        return redirect()->back();
    }

    private function handleRow(array $row)
    {
        if(empty($row['id'])) {
            //create new product
            $p = new Product([
               'name' => $row['name'],
               'price' => $row['price']
            ]);

            return $p->save();
        }

        $p = Product::find($row['id']);

        $p->update([
            'name'  => $row['name'],
            'price' => $row['price']
        ]);
    }
}
