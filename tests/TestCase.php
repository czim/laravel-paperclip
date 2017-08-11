<?php
namespace Czim\Paperclip\Test;

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
    }

}
