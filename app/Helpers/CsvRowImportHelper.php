<?php

namespace App\Helpers;

use App\Common\Importables\ExistingProductImport;
use App\Common\Importables\NewProductImport;
use App\Jobs\ImportProductFromCsv;
use JetBrains\PhpStorm\Pure;

class CsvRowImportHelper
{
    /**
     * the jobs to put in batch
     *
     * @var array
     */
    private array $jobs;

    /**
     * @param array $rows
     * @return ImportProductFromCsv
     */
    #[Pure] public function getDataFromFile(array $rows): ImportProductFromCsv
    {
        return new ImportProductFromCsv($this->rowsToImportable($rows));
    }

    /**
     * Parse rows to importable objects
     *
     * @param array $rows
     * @return array
     */
    #[Pure] private function rowsToImportable(array $rows): array {

        $importable = [];

        foreach ($rows as $row) {
            $importable[] = (trim($row['id']) === "") ?
                // new product
                new NewProductImport($row) :
                // existing product
                new ExistingProductImport($row);
            }

        return $importable;
    }

}
