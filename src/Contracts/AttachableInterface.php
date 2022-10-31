<?php

namespace Czim\Paperclip\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AttachableInterface
{
    /**
     * @return mixed {@link Model}
     */
    public function getKey();

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachedFiles(): array;

    /**
     * Marks that at least one attachment on the model has been updated and should be processed.
     */
    public function markAttachmentUpdated(): void;

    /**
     * Add the attached files to the model's attributes.
     */
    public function mergeFileAttributes(): void;
}
