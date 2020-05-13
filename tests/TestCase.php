<?php

namespace Czim\Paperclip\Test;

use Czim\Paperclip\Providers\PaperclipServiceProvider;
use Czim\Paperclip\Test\Helpers\Model\TestModel;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }


    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        /** @var Repository $config */
        $config = $app['config'];

        $config->set('paperclip', include(realpath(dirname(__DIR__) . '/config/paperclip.php')));

        $config->set('database.default', 'testbench');
        $config->set('database.connections.testbench', $this->getDatabaseConfigForSqlite());

        $config->set('filesystems.disks.paperclip', [
            'driver'     => 'local',
            'root'       => $this->getBasePath() . '/public/paperclip',
            'visibility' => 'public',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app): array
    {
        return [
            PaperclipServiceProvider::class,
        ];
    }

    /**
     * Returns the testing config for a (shared) SQLite connection.
     *
     * @return array
     */
    protected function getDatabaseConfigForSqlite(): array
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
    protected function setUpDatabase(): void
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255)->nullable();
            $table->string('attachment_file_name', 255)->nullable();
            $table->integer('attachment_file_size')->nullable();
            $table->string('attachment_content_type')->nullable();
            $table->timestamp('attachment_updated_at')->nullable();
            $table->string('attachment_variants', 255)->nullable();
            $table->string('image_file_name', 255)->nullable();
            $table->integer('image_file_size')->nullable();
            $table->string('image_content_type')->nullable();
            $table->timestamp('image_updated_at')->nullable();
            $table->nullableTimestamps();
        });
    }

    protected function getTestModel(): TestModel
    {
        return TestModel::create(['name' => 'Testing']);
    }

    protected function getTestModelWithAttachmentConfig(array $attachmentConfig): TestModel
    {
        $model = new TestModel([], $attachmentConfig);
        $model->save();

        return $model;
    }
}
