<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class ClassicModelsSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../../examples/classicmodels.xlsx';
//        $this->textOutput = false;
        $this->batchInsertSize = 10;
        $this->readChunkSize = 10;
        parent::run();
    }
}