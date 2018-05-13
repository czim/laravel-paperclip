<?php
namespace Czim\Paperclip\Test\Helpers\VariantStrategies;

use Czim\FileHandling\Variant\Strategies\AbstractVariantStrategy;

class TestNoChangesStrategy extends AbstractVariantStrategy
{

    /**
     * Performs manipulation of the file.
     *
     * @return bool|null|void
     */
    protected function perform()
    {
    }

}
