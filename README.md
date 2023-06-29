**IMPORTANT**

this project uses ([laravel batches](https://laravel.com/docs/10.x/queues)) to execute multiple jobs in a queue, so please make sure to run 

``php artisan queue:table``

``php artisan queue:batches``


``php artisan migrate``

before testing 

An artisan command has been added to create fixtures 

run ``php artisan app:create-csv`` with a numeric parameter 
to generate a file with a given number of rows


 
