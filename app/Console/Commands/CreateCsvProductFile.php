<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateCsvProductFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-csv {rows}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create a csv product file';

    /**
     * @var array|string[]
     */
    private array $header = ["id","name","price"];

    /**
     * the offset to set random products in the file
     */
    private const RANDOM_PRODS_OFFSET = 100;

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Support\Collection|mixed
     */
    private function getRandomProducts()
    {
        return Product::all()->random(self::RANDOM_PRODS_OFFSET);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rows = $this->argument('rows') - self::RANDOM_PRODS_OFFSET;
        $file = fopen('fixtures/' . time() . ".csv", "w");

        fputcsv($file, $this->header);

        // put products in the
        foreach (range(1, $rows) as $row) {
            $uuid = (rand(0, 1)) ? Str::uuid() : "";
            $name = Str::random(12);
            $price = rand(1, 99);
            fputcsv($file, array_combine($this->header, [$uuid, $name, $price]));
        }

        // add random products already in there
        $randomProds = $this->getRandomProducts();
        foreach ($randomProds as $randomProd) {
            $name = Str::random(12);
            $price = rand(1, 99);
            fputcsv($file, array_combine($this->header, [$randomProd->id, $name, $price]));
        }

        fclose($file);
    }
}
