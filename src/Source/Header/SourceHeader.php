<?php


namespace bfinlay\SpreadsheetSeeder;

use Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class SourceHeader
{
    /**
     * @var Row
     */
    private $sheetRow;

    /**
     * @var boolean
     */
    private $isCsv;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * Array of raw column names unaliased and un-skipped from the sheet
     *
     * @var string[]
     */
    private $headerRow;

    /**
     * Map of post-processed column names to column numbers
     *
     * @var int[]
     */
    public $columns;

    /**
     * Sparse array; map of column numbers to post-processed column names
     *
     * @ var string[]
     */
    public $columnNames;

    /**
     * Header constructor.
     * @param Row $headerRow
     * @param boolean $isCsv
     */
    public function __construct(Row $headerRow, $isCsv)
    {
        $this->sheetRow = $headerRow;
        $this->isCsv = $isCsv;
        $this->settings = resolve(SpreadsheetSeederSettings::class);
        $this->makeHeader();
    }

    private function makeHeader()
    {
        if (!empty($this->settings->mapping)) {
            $this->makeMappingHeader();
        } else {
            $this->makeSheetHeader();
        }
    }

    /**
     * The mapping header replaces a spreadsheet header
     * so if a spreadsheet header exists and if it contains skipper strings in the columns the skipper strings will not be evaluated.
     */
    private function makeMappingHeader() {
            $this->headerRow = $this->settings->mapping;
            foreach($this->headerRow as $key => $value) {
                $this->columns[$value] = $key;
                $this->columnNames[$key] = $value;
            }
    }

    private function makeSheetHeader() {

        $cellIterator = $this->sheetRow->getCellIterator();

        foreach ($cellIterator as $cell) {
            $columnName = $cell->getCalculatedValue();
            $this->headerRow[] = $columnName;
            if (!$this->skipColumn($columnName)) {
                $columnName = $this->columnAlias($columnName);
                $this->columns[$columnName] = count($this->headerRow) - 1;
                $this->columnNames[count($this->headerRow) - 1] = $columnName;
            }
        }
    }

    private function columnAlias($columnName) {
        $columnName = isset($this->settings->aliases[$columnName]) ? $this->settings->aliases[$columnName] : $columnName;
        return $columnName;
    }

    private function skipColumn($columnName) {
        return $this->settings->skipper == substr($columnName, 0, strlen($this->settings->skipper));
    }

    public function toArray() {
        return $this->columnNames;
    }
    
    public function rawColumns() {
        return $this->headerRow;
    }
}