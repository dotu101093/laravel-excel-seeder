<?php


namespace bfinlay\SpreadsheetSeeder\Destination\Database;


use bfinlay\SpreadsheetSeeder\SourceRow;

class Rows
{
    private $total = 0;
    private $count = 0;
    private $resultCount = 0;

    private $rows = [];
    private $rawRows = [];

    public function add(SourceRow $row) {
        if (!$row->isValid()) return;

        $this->rows[] = $row->toArray();
        $this->rawRows[] = $row->rawRow();

        $this->count++;
        $this->resultCount++;
    }

    public function tableRows() {
        return $this->rows;
    }


}