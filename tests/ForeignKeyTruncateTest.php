<?php

namespace bfinlay\SpreadsheetSeeder\Tests;

use bfinlay\SpreadsheetSeeder\DestinationTable;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeederServiceProvider;
use bfinlay\SpreadsheetSeeder\SpreadsheetSeederSettings;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ClassicModelsSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\DateTimeTest\DateTimeSeeder;
use bfinlay\SpreadsheetSeeder\Tests\Seeds\ForeignKeyTruncateTest\ForeignKeyTruncateSeeder;
use Illuminate\Database\QueryException;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Carbon;

class ForeignKeyTruncateTest extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');

        // and other test setup steps you need to perform
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [SpreadsheetSeederServiceProvider::class];
    }

    /** @test */
    public function it_runs_the_migrations()
    {
        $this->assertEquals([
            'id',
            'created_at',
            'updated_at',
            'user_id',
            'favorite_number'
        ], \Schema::getColumnListing('favorite_numbers'));
    }

    /**
     * Verify that truncating a table with a foreign key constraint throws a foreign key constraint exception
     *
     * @depends it_runs_the_migrations
     */
    public function test_integrity_constraints_prevent_truncation()
    {
        $this->seed(ForeignKeyTruncateSeeder::class);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('favorite_numbers', 2);

        $this->expectExceptionMessage('Integrity constraint violation: 19 FOREIGN KEY constraint failed');
        \DB::table('users')->truncate();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('favorite_numbers', 2);
    }


    /**
     * Create a new destination table and verify that truncation disables integrity constraints
     *
     * @depends it_runs_the_migrations
     */
    public function test_destination_table_truncation_observes_integrity_constraints()
    {
        $this->seed(ForeignKeyTruncateSeeder::class);

        $settings = resolve(SpreadsheetSeederSettings::class);
        $settings->truncateIgnoreForeign = false;

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('favorite_numbers', 2);

        $this->expectExceptionMessage('Integrity constraint violation: 19 FOREIGN KEY constraint failed');
        $usersTable = new DestinationTable('users');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('favorite_numbers', 2);
    }


    /**
     * Create a new destination table and verify that truncation disables integrity constraints
     *
     * @depends it_runs_the_migrations
     */
    public function test_destination_table_truncation_ignores_integrity_constraints()
    {
        $this->seed(ForeignKeyTruncateSeeder::class);

        $settings = resolve(SpreadsheetSeederSettings::class);
        $settings->truncateIgnoreForeign = true;

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('favorite_numbers', 2);

        $usersTable = new DestinationTable('users');

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('favorite_numbers', 2);
    }

}
