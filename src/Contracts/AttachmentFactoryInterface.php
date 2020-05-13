<?php

namespace Czim\Paperclip\Contracts;

interface AttachmentFactoryInterface
{

    /**
     * @param AttachableInterface $instance
     * @param string              $name
     * @param array               $config
     * @return AttachmentInterface
     */
    public function create(AttachableInterface $instance, $name, array $config = []);
}
