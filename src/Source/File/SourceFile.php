<?php


namespace bfinlay\SpreadsheetSeeder;


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use SplFileInfo;

class SourceFile implements \Iterator
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var string
     */
    private $fileType;

    /**
     * @var BaseReader
     */
    private $reader;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var Workbook
     */
    private $workbook;

    private $worksheetIterator;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if (!$this->shouldSkip()) $this->worksheetIterator = $this->getWorksheetIterator();
    }

    /**
     * Returns true if the file should be skipped.   Currently this only checks for a leading "~" character in the
     * filename, which indicates that the file is an Excel temporary file.
     *
     * @return bool
     */
    public function shouldSkip() {
        if (substr($this->file->getFilename(), 0, 1) === "~" ) return true;

        return false;
    }

    public function getWorksheetIterator() {
        if (!isset($this->workbook)) {
            $filename = $this->file->getPathname();
            $this->fileType = IOFactory::identify($filename);
            $this->reader = IOFactory::createReader($this->fileType);
            if ($this->fileType == "Csv" && !empty($this->settings->delimiter)) {
                $this->reader->setDelimiter($this->settings->delimiter);
            }
            $chunkFilter = new SourceChunkReadFilter();
            $this->reader->setReadFilter($chunkFilter);
            $this->workbook = $this->reader->load($filename);
        }
        return $this->workbook->getWorksheetIterator();
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        $worksheet = $this->worksheetIterator->current();
        if ($this->shouldSkipSheet($worksheet) ) {
            $this->next();
            $worksheet = $this->worksheetIterator->current();
        }

        $sourceSheet = new SourceSheet($worksheet, $this->settings);
        $sourceSheet->setFileType($this->fileType);
        if ($this->workbook->getSheetCount() == 1 && !$sourceSheet->titleIsTable()) {
            $sourceSheet->setTableName($this->file->getBasename("." . $this->file->getExtension()));
        }
        return $sourceSheet;
    }

    private function shouldSkipSheet($worksheet) {
        return $this->settings->skipper == substr($worksheet->getTitle(), 0, strlen($this->settings->skipper));
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->worksheetIterator->next();
        if (! $this->valid() ) return;
        $worksheet = $this->worksheetIterator->current();
        // If this worksheet is marked for skipping, recursively call this function for the next sheet
        if( $this->shouldSkipSheet($worksheet) ) $this->next();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->worksheetIterator->key();
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->worksheetIterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        return $this->worksheetIterator->rewind();
    }

    public function getFilename() {
        return $this->file->getFilename();
    }
    
    public function getPathname() {
        return $this->file->getPathname();
    }

    public function getDelimiter() {
        return $this->reader->getDelimiter();
    }
}