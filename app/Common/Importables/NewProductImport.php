<?php

namespace App\Common\Importables;

use App\Models\Product;

class NewProductImport implements Importable
{
    private array $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function import(): bool
    {
        $product = new Product([
            'name' => $this->values['name'],
            'price' => $this->values['price']
        ]);

        $product->save();

        return isset($product->id);
    }
}
