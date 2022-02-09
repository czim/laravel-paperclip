<?php

namespace Czim\Paperclip\Contracts;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;

interface FileHandlerFactoryInterface
{

    /**
     * Makes a file handler instance.
     *
     * @param string|null $storage
     * @return FileHandlerInterface
     */
    public function create($storage = null);
}
