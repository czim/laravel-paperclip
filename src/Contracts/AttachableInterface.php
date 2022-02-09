<?php

namespace Czim\Paperclip\Contracts;

interface AttachableInterface
{

    /**
     * @return mixed
     */
    public function getKey();

    /**
     * @return AttachmentInterface[]
     */
    public function getAttachedFiles();

    /**
     * Marks that at least one attachment on the model has been updated and should be processed.
     */
    public function markAttachmentUpdated();
}
