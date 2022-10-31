<?php

namespace Czim\Paperclip\Contracts;

interface AttachmentFactoryInterface
{
    /**
     * @param AttachableInterface  $instance
     * @param string               $name
     * @param array<string, mixed> $config
     * @return AttachmentInterface
     */
    public function create(AttachableInterface $instance, string $name, array $config = []): AttachmentInterface;
}
