<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductUpload extends TestCase
{

    use RefreshDatabase;

    private array $header;
    private array $csvData;
    private $csvFile;

    public function setUp(): void
    {

        //create fake data to put into csv file
        $this->header = ["id","name","price"];
        $this->csvData = [
            [
                Str::uuid(),
                'product 1',
                 3.99
            ],
            [
                Str::uuid(),
                'product 2',
                16
            ],
            [
                Str::uuid(),
                'name' => 'new product',
                'price' => 11.60
            ]

        ];
        parent::setUp();
    }

    public function test_should_upload_csv_file(): void
    {
        // Arrange
        Storage::fake('csv');
        $file = UploadedFile::fake()->create('test.csv');

        //append csv rows
        $handle = $file->openFile('a');
        $handle->fputcsv($this->header);
        foreach ($this->csvData as $csvRow) {
            $handle->fputcsv($csvRow);
        }

        // Act
        $this->post('/upload-products', ['products' => $file]);

        // Assert that file has been uploaded
        Storage::disk('csv')->assertExists($file->hashName());

        // Check if you have any DB entry against that
        $this->assertDatabaseCount('products', count($this->csvData));
    }
}
