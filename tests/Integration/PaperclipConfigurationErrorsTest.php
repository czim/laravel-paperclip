<?php
namespace Czim\Paperclip\Test\Integration;

use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Test\Helpers\Hooks\SpyCallableHook;
use Czim\Paperclip\Test\Helpers\VariantStrategies\TestNoChangesStrategy;
use Czim\Paperclip\Test\Helpers\VariantStrategies\TestTextToHtmlStrategy;
use Czim\Paperclip\Test\ProvisionedTestCase;
use SplFileInfo;

class PaperclipConfigurationErrorsTest extends ProvisionedTestCase
{

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /paperclip storage disk 'paperclip' is not configured/i
     */
    function it_throws_an_exception_if_the_filesystem_disk_configured_does_not_exist()
    {
        $this->app['config']->set('filesystems.disks', []);

        $this->getTestModel();
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /paperclip storage disk invalid or null/i
     */
    function it_throws_an_exception_if_the_paperclip_storage_disk_is_not_set()
    {
        $this->app['config']->set('filesystems.default', null);
        //$this->app['config']->set('filesystems.disks', []);
        $this->app['config']->set('paperclip.storage.disk', null);

        $this->getTestModel();
    }

}
