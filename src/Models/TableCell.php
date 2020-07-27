<?php


namespace bfinlay\SpreadsheetSeeder\Models;


class TableCell
{
    public $value;
    public $formula;
    public $formatting = false; // placeholder

    public function __construct($value, $formula)
    {
        $this->value = $value;
        $this->formula = $formula;
    }
}