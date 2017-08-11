<?php
namespace Czim\Paperclip\Contracts\Path;

use Czim\Paperclip\Contracts\AttachmentInterface;

interface InterpolatorInterface
{

    /**
     * Interpolate a string.
     *
     * @param string              $string
     * @param AttachmentInterface $attachment
     * @param string|null         $variant
     * @return string
     */
    public function interpolate($string, AttachmentInterface $attachment, $variant = null);

}
