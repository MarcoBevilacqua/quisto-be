<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportResult extends TestCase
{

    use RefreshDatabase;
    private array $header;

    public function setUp(): void
    {
        //create fake data to put into csv file
        $this->header = ["id","name","price"];
        parent::setUp();
    }

    private function getFixtures(File $file, string $fixtureFileName): File{
        $res = $file->openFile('a');

        $res->fputcsv($this->header);

        $handle = fopen('fixtures/'. $fixtureFileName .'.csv', 'r');
        while (($row = fgetcsv($handle)) !== FALSE) {
            $res->fputcsv($row);
        }
        fclose($handle);

        return $file;
    }
    /**
     * A basic feature test example.
     */
    public function test_simple_update_increments_import_results(): void
    {
        // Arrange (fake upload and fill file with fixture)
        Storage::fake('csv');
        $file = UploadedFile::fake()->create('test.csv');
        $file = $this->getFixtures($file, 'new_products');

        // Act
        $this->post('/api/csv/import', ['products' => $file]);

        // Check if you have any DB entry against that
        $this->assertDatabaseCount('products', 3);

        // Check import results
        $this->assertDatabaseCount('import_results', 1);
    }

    public function test_should_update_product_from_csv(): void
    {
        // Arrange
        Storage::fake('csv');
        $file = UploadedFile::fake()->create('test.csv');
        $product = Product::factory()->create();
        $res = $file->openFile('w');

        $res->fputcsv($this->header);
        $res->fputcsv([
            'id'    => $product->id,
            'name'  => 'updated_name',
            'price' => 1.99
        ]);

        // Act
        $this->post('/api/csv/import', ['products' => $file]);

        // Check if database count has changed
        $this->assertDatabaseCount('products', 1);

        //check if product has been updated
        $this->assertDatabaseHas('import_results', [
            'updated'  => 1,
            'created' => 0
        ]);
    }
}
