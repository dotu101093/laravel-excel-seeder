<?php


namespace bfinlay\SpreadsheetSeeder;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class Row
{
    /**
     * @var Row
     */
    private $sheetRow;

    /**
     * @var string[]
     */
    private $columnNames;

    /**
     * @var array
     */
    private $rowArray;

    /**
     * @var array
     */
    private $rawRowArray;

    /**
     * @var boolean
     */
    private $isValid = false;

    /**
     * @var boolean
     */
    private $isNull = false;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * SourceRow constructor.
     * @param Row $row
     * @param string[] $columnNames A sparse array mapping column index => column name
     */
    public function __construct(Row $row, $columnNames)
    {
        $this->sheetRow = $row;
        $this->columnNames = $columnNames;
        $this->settings = resolve(SpreadsheetSeederSettings::class);
        $this->makeRow();
    }

    private function makeRow() {
        $nullRow = true;
        $cellIterator = $this->sheetRow->getCellIterator();
        $colIndex = 0;
        foreach($cellIterator as $cell) {
            if (isset($this->columnNames[$colIndex])) {
                $value = $cell->getCalculatedValue();
                if (!is_null($value)) $nullRow = false;
                $columnName = $this->columnNames[$colIndex];
                $this->rawRowArray[$colIndex] = $value;
                $this->rowArray[$columnName] = $this->transformValue($columnName, $value);
            }
            else {
                $this->rawRowArray[$colIndex] = "";
            }
            $colIndex++;
        }
        if ($nullRow) {
            $this->isValid = false;
            $this->isNull = true;
        }
        else {
            $this->addTimestamps();
            $this->isValid = $this->validate();
        }
    }
}