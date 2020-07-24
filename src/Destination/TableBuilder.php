<?php


namespace bfinlay\SpreadsheetSeeder\Destination;


interface TableBuilder
{
    public function createTable($tableName);

    public function setColumns($columns);

    public function addRow($row);

    public function write();
}