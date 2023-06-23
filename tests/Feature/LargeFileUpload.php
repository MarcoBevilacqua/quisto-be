<?php

namespace Tests\Feature;

use Illuminate\Bus\PendingBatch;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LargeFileUpload extends TestCase
{
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
    public function test_batch_job_is_dispatched(): void
    {
        // Arrange
        Bus::fake();
        Storage::fake('csv');
        $file = UploadedFile::fake()->create('test.csv');

        // get file content from fixtures
        $file = $this->getFixtures($file, 'eight_new_products');

        // Act
        $this->post('/upload-products', ['products' => $file]);

        // assert batch
        Bus::assertBatched(function(PendingBatch $batch) {
            return $batch->name === 'csv-product-import';
        });
    }
}
