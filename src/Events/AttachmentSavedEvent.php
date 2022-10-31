<?php

declare(strict_types=1);

namespace Czim\Paperclip\Events;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;

class AttachmentSavedEvent
{
    public function __construct(
        public readonly AttachmentInterface $attachment,
        public readonly StorableFileInterface $uploadedFile,
    ) {
    }
}
