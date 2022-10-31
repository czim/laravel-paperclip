<?php

declare(strict_types=1);

namespace Czim\Paperclip\Events;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Throwable;

class ProcessingExceptionEvent
{
    /**
     * @param Throwable             $exception
     * @param StorableFileInterface $source
     * @param string                $variant
     * @param array<string, mixed>  $information
     */
    public function __construct(
        public readonly Throwable $exception,
        public readonly StorableFileInterface $source,
        public readonly string $variant,
        public readonly array $information,
    ) {
    }
}
