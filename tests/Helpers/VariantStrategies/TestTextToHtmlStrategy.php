<?php
namespace Czim\Paperclip\Test\Helpers\VariantStrategies;

use Czim\FileHandling\Variant\Strategies\AbstractVariantStrategy;

class TestTextToHtmlStrategy extends AbstractVariantStrategy
{

    /**
     * Performs manipulation of the file.
     *
     * @return bool|null|void
     */
    protected function perform()
    {
        //$path = $this->file->path();

        $this->file->setMimeType('text/html');
        $this->file->setName('source.htm');
    }

}
