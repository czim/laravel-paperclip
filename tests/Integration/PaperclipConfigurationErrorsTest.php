<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Integration;

use Czim\Paperclip\Test\ProvisionedTestCase;
use RuntimeException;

class PaperclipConfigurationErrorsTest extends ProvisionedTestCase
{

    /**
     * @test
     */
    function it_throws_an_exception_if_the_filesystem_disk_configured_does_not_exist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches("/paperclip storage disk 'paperclip' is not configured/i");

        $this->app['config']->set('filesystems.disks', []);

        $this->getTestModel();
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_the_paperclip_storage_disk_is_not_set()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/paperclip storage disk invalid or null/i');

        $this->app['config']->set('filesystems.default', null);
        //$this->app['config']->set('filesystems.disks', []);
        $this->app['config']->set('paperclip.storage.disk', null);

        $this->getTestModel();
    }

}
