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
        var_dump($this->values['id']);
        $product = Product::updateOrCreate(
            ["id"    => $this->values['id']],
                [
                    "id"    => $this->values['id'],
                    "name"  => $this->values['name'],
                    "price" => $this->values['price']
                ]
        );

        return isset($product->id);
    }
}
