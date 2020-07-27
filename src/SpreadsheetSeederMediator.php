<?php


namespace bfinlay\SpreadsheetSeeder;

class SpreadsheetSeederMediator
{
    /**
     * @var SpreadsheetSeeder
     */
    private $seeder;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var SourceFile
     */
    private $sourceFile;

    /**
     * @var SourceSheet
     */
    private $sourceSheet;

    /**
     * @var DestinationTable
     */
    private $seedTable;

    /**
     * @var string[]
     */
    public $tablesSeeded;

    /**
     * @var string[][]
     */
    private $rows = [];

    /**
     * @var string[][]
     */
    private $rawRows = [];

    /**
     * @var \SplFileObject
     */
    private $textOutputFile;

    /**
     * @var int
     */
    private $resultCount = 0;
    private $count = 0;
    private $total = 0;

    public function __construct(SpreadsheetSeeder $seeder)
    {
        $this->seeder = $seeder;
        $this->settings = resolve(SpreadsheetSeederSettings::class);
    }

    /**
     * Run the class
     *
     * @return void
     */
    public function run()
    {
        $fileIterator = new FileIterator();
        if (!$fileIterator->count()) {
            $this->seeder->console('No spreadsheet file given', 'error');
            return;
        }

        foreach ($fileIterator as $this->sourceFile) {
            $this->seed();
        }

        $this->seeder->tablesSeeded = $this->tablesSeeded;
    }

    public function seed()
    {
        foreach ($this->sourceFile as $this->sourceSheet) {
            $this->checkTable();
            foreach ($this->sourceSheet as $this->sourceChunk) {
                $this->processRows();
                $this->insertRows();
                $this->writeTextOutputTable();
            }
            $this->outputResults();
        }
    }

    private function checkColumns()
    {
        if ($this->sourceSheet->isCsv() && count($this->sourceSheet->getHeader()->toArray()) == 1)
            $this->seeder->console('Found only one column in header.  Maybe the delimiter set for the CSV is incorrect: [' . $this->sourceFile->getDelimiter() . ']');
    }

    private function checkTable()
    {
        $tableName = isset($this->settings->tablename) ? $this->settings->tablename : $this->sourceSheet->getTableName();
        $this->seedTable = new DestinationTable($tableName, $this->settings);


        if (!$this->seedTable->exists()) {
            $this->seeder->console('Table "' . $tableName . '" could not be found in database', 'error');
            return;
        }

        $this->tablesSeeded[] = $tableName;
    }

    /**
     * Process each row of the data source
     *
     * @return void
     */
    private function processRows()
    {
        foreach ($this->sourceChunk as $row) {
            $this->total++;

            if (!$row->isValid()) continue;

            $this->rows[] = $row->toArray();
            $this->rawRows[] = $row->rawRow();

            $this->count++;
            $this->resultCount++;
        }
    }

    private function openTextOutputFile()
    {
        $pathname = '';
        $path_parts = pathinfo($this->sourceFile->getPathname());
        if (strlen($path_parts['dirname']) > 0) $pathname = $path_parts['dirname'] . '/';
        $pathname = $pathname . $path_parts['filename'];

        $mkdirResult = false;
        if (!(is_dir($pathname))) {
            $mkdirResult = mkdir($pathname, 0777, true);
        }

        $filename = $pathname . '/' . $this->sourceSheet->getTableName() . '.' . $this->settings->textOutputFileExtension;

        $this->textOutputFile = new \SplFileObject($filename, 'w');
    }

    private function writeTextOutputTable()
    {
        if (!$this->settings->textOutput) {
            return;
        }

        $textOutputFile = $this->openTextOutputFile();
        $table = new TextOutputTable(
            $this->textOutputFile,
            $this->sourceSheet->getTableName(),
            $this->sourceSheet->getHeader()->rawColumns(),
            $this->rawRows
        );
        $table->write();

        $this->rawRows = [];
    }

    /**
     * Insert rows into table
     *
     * @return void
     */
    private function insertRows()
    {
        if (empty($this->rows)) return;

        try {
            $this->seedTable->insertRows($this->rows);

            $this->rows = [];

            $this->count = 0;
        } catch (\Exception $e) {
            $this->seeder->console('Rows of the file "' . $this->sourceFile->getFilename() . '" sheet "' . $this->sourceSheet->getTitle() . '" has failed to insert in table "' . $this->seedTable->getName() . '": ' . $e->getMessage(), 'error');

            die();
        }
    }

    /**
     * Output the result of seeding
     *
     * @return void
     */
    private function outputResults()
    {
        $this->seeder->console($this->resultCount . ' of ' . $this->total . ' rows has been seeded in table "' . $this->seedTable->getName() . '"');
        $this->total = 0;
        $this->resultCount = 0;
    }
}
