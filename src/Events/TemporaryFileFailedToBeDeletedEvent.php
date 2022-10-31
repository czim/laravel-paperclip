<?php

declare(strict_types=1);

namespace Czim\Paperclip\Events;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Throwable;

class TemporaryFileFailedToBeDeletedEvent
{
    public function __construct(
        public readonly StorableFileInterface $file,
        public readonly ?Throwable $exception = null,
    ) {
    }
}
