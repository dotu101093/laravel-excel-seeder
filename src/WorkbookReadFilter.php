<?php


namespace bfinlay\SpreadsheetSeeder\Source\File;


use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class WorkbookReadFilter implements IReadFilter
{
    public function readCell($column, $row, $worksheetName = '') {
        //  Only read the heading row
        return $row == 1;
    }
}