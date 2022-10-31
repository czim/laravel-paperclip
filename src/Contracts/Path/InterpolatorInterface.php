<?php

namespace Czim\Paperclip\Contracts\Path;

use Czim\Paperclip\Contracts\AttachmentDataInterface;

interface InterpolatorInterface
{
    public function interpolate(string $string, AttachmentDataInterface $attachment, ?string $variant = null): string;
}
