<?php


namespace bfinlay\SpreadsheetSeeder\Destination\Database;


use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;
use Illuminate\Support\Facades\DB;

class Columns
{
    private $columns = [];
    private $doctrineColumns = [];
    private $settings;

    public function __construct($tableName)
    {
        $this->settings = resolve(SpreadsheetSeederSettings::class);

        if (! isset($this->columns)) {
            $this->columns = DB::getSchemaBuilder()->getColumnListing( $tableName );
            $doctrineColumns = DB::getSchemaBuilder()->getConnection()->getDoctrineSchemaManager()->listTableColumns($tableName);

            /*
             * Doctrine DBAL 2.11.x-dev does not return the column name as an index in the case of mixed case (or uppercase?) column names
             * In sqlite in-memory database, DBAL->listTableColumns() uses the lowercase version of the column name as a column index
             * In postgres, it uses the lowercase version of the mixed-case column name and places '"' around the name (for the mixed-case name only)
             * The solution here is to iterate through the columns to retrieve the column name and use that to build a new index.
             */
            foreach ($doctrineColumns as $column) {
                $this->doctrineColumns[$column->getName()] = $column;
            }
        }
    }

    public function exists($columnName) {
        return in_array($columnName, $this->columns);
    }

    public function defaultValue($column) {
        $c = $this->doctrineColumns[$column];

        // return default value for column if set
        if ($c->getDefault()) return $c->getDefault();

        // if column is auto-incrementing return null and let database set the value
        if ($c->getAutoincrement()) return null;

        // if column accepts null values, return null
        if (! $c->getNotnull()) return null;

        // if column is numeric, return 0
        $doctrineNumericValues = ['smallint', 'integer', 'bigint', 'decimal', 'float'];
        if (in_array($c->getType()->getName(), $doctrineNumericValues)) return 0;

        // if column is date or time type return
        $doctrineDateValues = ['date', 'date_immutable', 'datetime', 'datetime_immutable', 'datetimez', 'datetimez_immutable', 'time', 'time_immutable', 'dateinterval'];
        if (in_array($c->getType()->getName(), $doctrineDateValues)) {
            if ($this->settings->timestamps) return date('Y-m-d H:i:s');
            else return 0;
        }

        // if column is boolean return false
        if ($c->getType()->getName() == "boolean") return false;

        // else return empty string
        return "";
    }
}