<?php

namespace Czim\Paperclip\Contracts\Path;

use Czim\Paperclip\Contracts\AttachmentDataInterface;

interface InterpolatorInterface
{

    /**
     * Interpolate a string.
     *
     * @param string                  $string
     * @param AttachmentDataInterface $attachment
     * @param string|null             $variant
     * @return string
     */
    public function interpolate($string, AttachmentDataInterface $attachment, $variant = null);
}
