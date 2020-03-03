<?php

namespace Czim\Paperclip\Events;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;

class AttachmentSavedEvent
{
    /**
     * @var AttachmentInterface
     */
    protected $attachment;

    /**
     * @var StorableFileInterface
     */
    protected $uploadedFile;

    public function __construct(AttachmentInterface $attachment, StorableFileInterface $uploadedFile)
    {
        $this->attachment   = $attachment;
        $this->uploadedFile = $uploadedFile;
    }

    public function getAttachment(): AttachmentInterface
    {
        return $this->attachment;
    }

    public function getUploadedFile(): StorableFileInterface
    {
        return $this->uploadedFile;
    }
}
