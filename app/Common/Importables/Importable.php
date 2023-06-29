<?php

namespace App\Common\Importables;

interface Importable
{
    public function __construct(array $values);
    public function import(): bool;
}
