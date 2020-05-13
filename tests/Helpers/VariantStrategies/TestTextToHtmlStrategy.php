<?php

namespace Czim\Paperclip\Test\Helpers\VariantStrategies;

use Czim\FileHandling\Variant\Strategies\AbstractVariantStrategy;

class TestTextToHtmlStrategy extends AbstractVariantStrategy
{
    /**
     * Performs manipulation of the file.
     *
     * @return bool|null
     */
    protected function perform(): ?bool
    {
        $this->file->setMimeType('text/html');
        $this->file->setName('source.htm');

        return null;
    }
}
