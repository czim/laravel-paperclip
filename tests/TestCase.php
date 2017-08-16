<?php
namespace Czim\Paperclip\Test;

use Illuminate\Support\Facades\Schema;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('paperclip', include(realpath(dirname(__DIR__) . '/config/paperclip.php')));

        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', $this->getDatabaseConfigForSqlite());
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Returns the testing config for a (shared) SQLite connection.
     *
     * @return array
     */
    protected function getDatabaseConfigForSqlite()
    {
        return [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ];
    }

    /**
     * Sets up the database for testing. This includes migration and standard seeding.
     */
    protected function setUpDatabase()
    {
        Schema::create('test_models', function($table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->string('attachment_file_name', 255)->nullable();
            $table->integer('attachment_file_size')->nullable();
            $table->string('attachment_content_type')->nullable();
            $table->timestamp('attachment_updated_at')->nullable();
            $table->string('attachment_variants', 255)->nullable();
            $table->nullableTimestamps();
        });
    }

}
