<?php


namespace bfinlay\SpreadsheetSeeder\Tests\Seeds;

use bfinlay\SpreadsheetSeeder\SpreadsheetSeeder;

class FakeNames100kXlsxSeeder extends SpreadsheetSeeder
{
    public function run()
    {
        // path is relative to base_path which is laravel-excel-seeder/vendor/orchestra/testbench-core/laravel
        $this->file = '/../../../bfinlay/laravel-excel-seeder-test-data/fake_names_100k.xlsx';
        $this->tablename = 'fake_names';
        $this->aliases = ['Number' => 'id'];
        $this->textOutput = false;
        parent::run();
    }
}