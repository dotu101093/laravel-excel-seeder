<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class OfficesSingleNamedSheetSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../../examples/offices-named-sheet.xlsx';
        $this->textOutput = false;
        parent::run();
    }
}