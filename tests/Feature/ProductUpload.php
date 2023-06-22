<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Factories\ProductFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductUpload extends TestCase
{
    use RefreshDatabase;

    private array $header;
    private array $csvData;

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

    public function test_should_upload_csv_file(): void
    {
        // Arrange (fake upload and fill file with fixture)
        Storage::fake('csv');
        $file = UploadedFile::fake()->create('test.csv');
        $file = $this->getFixtures($file, 'new_products');

        // Act
        $this->post('/upload-products', ['products' => $file]);

        // Assert that file has been uploaded
        Storage::disk('csv')->assertExists($file->hashName());
    }

    public function test_should_create_new_product_from_csv(): void
    {
        // Arrange (fake upload and fill file with fixture)
        Storage::fake('csv');
        $file = UploadedFile::fake()->create('test.csv');

        $file = $this->getFixtures($file, 'new_products');

        // test DB is empty
        $this->assertDatabaseCount('products', 0);

        // Act
        $this->post('/upload-products', ['products' => $file]);

        // Check if you have any DB entry against that
        $this->assertDatabaseCount('products', 3);
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

        // test DB is empty
        $this->assertDatabaseCount('products', 1);

        // Act
        $this->post('/upload-products', ['products' => $file]);

        // Check if database count has changed
        $this->assertDatabaseCount('products', 1);

        //check if product has been updated
        $this->assertDatabaseHas('products', [
            'name'  => 'updated_name',
            'price' => 1.99
        ]);
    }
}
