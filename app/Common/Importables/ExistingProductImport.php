<?php

namespace App\Common\Importables;

use App\Models\Product;

class ExistingProductImport implements Importable
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function import(): bool
    {
        $product = Product::updateOrCreate(
            ["id"    => $this->values['id']],
            [
                "name"  => $this->values['name'],
                "price" => $this->values['price']
            ]
        );

        if(!$product->wasRecentlyCreated && $product->wasChanged()){
            return false;
        }

        if(!$product->wasRecentlyCreated && !$product->wasChanged()){

            return false;
        }

        return true;
    }
}
