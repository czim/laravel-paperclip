<?php

namespace Czim\Paperclip\Contracts;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;

interface FileHandlerFactoryInterface
{
    public function create(?string $storage = null): FileHandlerInterface;
}
