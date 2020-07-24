<?php


namespace bfinlay\SpreadsheetSeeder;


class TextOutputTable
{
    /**
     * @var \SplFileObject
     */
    private $file;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string[]
     */
    private $header;

    /**
     * @var array
     */
    private $rows;

    /**
     * @var int[]
     */
    private $columnWidths;

    /**
     * @var int
     */
    private $columnPadding = 2;

    /*
     * borders - terminology
     *
     *  | Column 1 | Column 2 | Column 3 | <- header
     *  |----------|----------|----------| <- header underline
     *  |  Cell 1  |  Cell 2  |  Cell 3  | <- row
     *   <- outside left column separator
     *               <- column separator
     *                         <- column separator
     *                                     <- outside right column separator
     */


    /*
     * examples:
     *
     * alternate - outside left, right = '|', column = '|'
     *  | Column 1 | Column 2 | Column 3 |
     *
     * alternates - outside left and right are '', column = '|':
     *    Column 1 | Column 2 | Column 3
     */
    private $headerOutsideLeftColumnSeparator = '|';
    private $headerColumnSeparator = '|';
    private $headerOutsideRightColumnSeparator = '|';

    /*
     * examples:
     *
     * alternate - underline = '-', column = '|', outside left and right = '|'
     *  |-----------|----------|----------| <- underline character (characters between column separators)
     *
     * alternate - outside left and right = '', underline = '-', column = '|'
     *   -----------|----------|----------
     *
     * alternate - column = '+', outside left and right = '', underline = '-'
     *   -----------+----------+----------
     *
     * alternate - underline = '=', column = '|', outside left and right = ''
     *   ===========|==========|==========
     */
    private $headerUnderlineCharacter = '-';
    private $headerUnderlineOutsideLeftColumnSeparator = '|';
    private $headerUnderlineColumnSeparator = '|';
    private $headerUnderlineOutsideRightColumnSeparator = '|';

    /*
     * examples:
     *
     * alternate - outside left, right = '|', column = '|'
     *  |  Cell 1  |  Cell 2  |  Cell 3  |
     *
     * alternates - outside left and right are '', column = '|':
     *     Cell 1  |  Cell 2  |  Cell 3
     */
    private $rowOutsideLeftColumnSeparator = '|';
    private $rowColumnSeparator = '|';
    private $rowOutsideRightColumnSeparator = '|';

    /**
     * TextOutputTable constructor.
     * @param \SplFileObject $file
     * @param string $tableName
     * @param string[] $header
     * @param array $rows
     */
    public function __construct(\SplFileObject $file, $tableName, $header, $rows)
    {
        $this->file = $file;
        $this->tableName = $tableName;
        $this->header = $header;
        $this->rows = $rows;
    }
    
    public function write() {
        if (! $this->file->isWritable()) throw new Exception('File ' . $this->file->getFilename() . ' is not open for writing.');
        
        $this->columnWidths();
        $this->writeTableName();
        $this->writeTableHeader();
        $this->writeTableRows();
    }

    private function writeTableName() {
        $this->file->fwrite($this->tableName . "\n");
        $border = str_repeat('=', strlen($this->tableName));
        $this->file->fwrite($border . "\n\n");
    }

    private function writeTableHeader() {
        foreach ($this->header as $index => $columnName) {
            $columnHeader = str_pad($columnName, $this->columnWidths[$index] + $this->columnPadding, " ", STR_PAD_BOTH);
            $columnSeparator = ($index > 0) ? $columnSeparator = $this->headerColumnSeparator : $this->headerOutsideLeftColumnSeparator;
            $this->file->fwrite($columnSeparator . $columnHeader);
        }
        $this->file->fwrite($this->headerOutsideRightColumnSeparator . "\n");

        foreach ($this->header as $index => $columnName) {
            $columnHeader = str_repeat($this->headerUnderlineCharacter, $this->columnWidths[$index] + $this->columnPadding);
            $columnSeparator = ($index > 0) ? $columnSeparator = $this->headerUnderlineColumnSeparator : $columnSeparator = $this->headerUnderlineOutsideLeftColumnSeparator;
            $this->file->fwrite($columnSeparator . $columnHeader);
        }
        $this->file->fwrite($this->headerUnderlineOutsideRightColumnSeparator . "\n");
    }

    private function writeTableRows() {
        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                $valueCell = str_pad($value, $this->columnWidths[$index]);
                $columnSeparator = ($index > 0) ? $columnSeparator = $this->rowColumnSeparator : $columnSeparator = $this->rowOutsideLeftColumnSeparator;
                $this->file->fwrite($columnSeparator . ' ' . $valueCell . ' ');
            }
            $this->file->fwrite($this->rowOutsideRightColumnSeparator . "\n");
        }
        $this->file->fwrite('(' . count($this->rows) . " rows)\n\n");
    }

    private function columnWidths() {
        foreach ($this->header as $index => $columnName) {
            $this->columnWidths[$index] = max(strlen($columnName),1);
        }

        foreach ($this->rows as $row) {
            foreach ($row as $index => $value) {
                if (strlen($value) > $this->columnWidths[$index])
                    $this->columnWidths[$index] = strlen($value);
            }
        }
    }
}