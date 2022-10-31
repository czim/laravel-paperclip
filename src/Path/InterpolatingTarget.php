<?php

declare(strict_types=1);

namespace Czim\Paperclip\Path;

use Czim\FileHandling\Storage\Path\Target;
use Czim\Paperclip\Contracts\AttachmentDataInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;

/**
 * Extension of a basic target that interpolates paths on the fly.
 * This may be used to reliably interpolate abstract variant paths,
 * thus removing risks involved around manipulating concretes.
 */
class InterpolatingTarget extends Target
{
    protected readonly InterpolatorInterface $interpolator;
    protected readonly AttachmentDataInterface $attachment;

    /**
     * @param InterpolatorInterface   $interpolator
     * @param AttachmentDataInterface $attachment
     * @param string                  $path
     * @param string|null             $variantPath use :variant as a placeholder
     */
    public function __construct(
        InterpolatorInterface $interpolator,
        AttachmentDataInterface $attachment,
        string $path,
        ?string $variantPath = null,
    ) {
        $this->interpolator = $interpolator;
        $this->attachment   = $attachment;

        parent::__construct($path, $variantPath);
    }


    /**
     * {@inheritdoc}
     */
    public function original(): string
    {
        return $this->interpolator->interpolate($this->originalPath, $this->attachment);
    }

    /**
     * {@inheritdoc}
     */
    public function variant($variant): string
    {
        return $this->interpolator->interpolate($this->getVariantPath(), $this->attachment, $variant);
    }

    protected function getVariantPath(): string
    {
        return $this->variantPath ?: $this->originalPath;
    }
}
