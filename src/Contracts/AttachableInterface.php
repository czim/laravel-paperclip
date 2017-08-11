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

}
