<?php

namespace Czim\Paperclip\Test\Helpers\VariantStrategies;

use Czim\FileHandling\Variant\Strategies\AbstractVariantStrategy;

class TestNoChangesStrategy extends AbstractVariantStrategy
{
    /**
     * Performs manipulation of the file.
     *
     * @return bool|null
     */
    protected function perform(): ?bool
    {
        return null;
    }
}
