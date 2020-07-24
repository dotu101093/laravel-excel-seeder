<?php


namespace bfinlay\SpreadsheetSeeder;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\DB;

class Table
{
    private $name;

    /**
     * @var bool
     */
    private $exists;

    /**
     * @var SpreadsheetSeederSettings
     */
    private $settings;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * @var array
     */
    private $rows;

    /**
     * 
     * See methods in vendor/doctrine/dbal/lib/Doctrine/DBAL/Schema/Column.php
     * 
     * @var Column
     */
    private $doctrineColumns;

    public function __construct($name, SpreadsheetSeederSettings $settings)
    {
        $this->name = $name;
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if ($this->exists() && $this->settings->truncate) $this->truncate();
    }

    public function getName() {
        return $this->name;
    }

    public function exists() {
        if (isset($this->exists)) return $this->exists;
        $this->exists =  self::tableExists( $this->name );

        return $this->exists;
    }

    public static function tableExists($name)
    {
        return DB::getSchemaBuilder()->hasTable( $name );
    }

    public function truncate( $foreignKeys = TRUE ) {
        if( ! $foreignKeys ) DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

        DB::table( $this->name )->truncate();

        if( ! $foreignKeys ) DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    private function transformNullCellValue($columnName, $value) {
        if (is_null($value)) {
            $value = $this->defaultValue($columnName);
        }
        return $value;
    }

    private function checkRows($rows) {
        foreach ($rows as $row) {
            $tableRow = [];
            foreach ($row as $column => $value) {
                if ($this->columnExists($column)) $tableRow[$column] = $this->transformNullCellValue($column, $value);
            }
            $this->rows[] = $tableRow;
        }
    }

    public function insertRows($rows) {
        if( empty($rows) ) return;

        $this->checkRows($rows);

        DB::table( $this->name )->insert( $this->rows );
    }
}