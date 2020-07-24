<?php


namespace bfinlay\SpreadsheetSeeder\Destination\Database;


use bfinlay\SpreadsheetSeeder\DestinationTable;

class Inserter
{
    private $table;

    public function __construct(DestinationTable $table)
    {
        $this->table = $table;
    }

    public function insertRows(Rows $builder) {
        if (empty( $builder->tableRows() )) return;

        $this->table->insertRows($builder->tableRows());
    }
}