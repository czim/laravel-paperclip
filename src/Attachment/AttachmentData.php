<?php

declare(strict_types=1);

namespace Czim\Paperclip\Attachment;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentDataInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Data object that reflects a previous state of the attachment.
 * This is used for queued deletion of a previously stored attachment.
 */
class AttachmentData implements AttachmentDataInterface
{
    /**
     * @param string                                  $name
     * @param array<string, mixed>                    $config
     * @param array<string, mixed>                    $attributes
     * @param array<string, array<string, mixed>>     $variants
     * @param mixed                                   $instanceKey
     * @param class-string<AttachableInterface&Model> $instanceClass
     */
    public function __construct(
        protected readonly string $name,
        protected readonly array $config,
        protected readonly array $attributes,
        protected readonly array $variants,
        protected readonly mixed $instanceKey,
        protected readonly string $instanceClass,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function createdAt(): ?string
    {
        if (! array_key_exists('created_at', $this->attributes)) {
            return null;
        }

        return $this->attributes['created_at'];
    }

    /**
     * {@inheritDoc}
     */
    public function updatedAt(): ?string
    {
        if (! array_key_exists('updated_at', $this->attributes)) {
            return null;
        }

        return $this->attributes['updated_at'];
    }

    /**
     * {@inheritDoc}
     */
    public function contentType(): ?string
    {
        if (! array_key_exists('content_type', $this->attributes)) {
            return null;
        }

        return $this->attributes['content_type'];
    }

    /**
     * {@inheritDoc}
     */
    public function size(): ?int
    {
        if (! array_key_exists('file_size', $this->attributes)) {
            return null;
        }

        return $this->attributes['file_size'];
    }

    /**
     * {@inheritDoc}
     */
    public function originalFilename(): ?string
    {
        if (! array_key_exists('file_name', $this->attributes)) {
            return null;
        }

        return $this->attributes['file_name'];
    }

    /**
     * {@inheritDoc}
     */
    public function variantsAttribute(): array
    {
        if (! array_key_exists('variants', $this->attributes)) {
            return [];
        }

        return $this->attributes['variants'];
    }

    /**
     * {@inheritDoc}
     */
    public function variantFilename(?string $variant): string|false
    {
        if (
            ! array_key_exists($variant, $this->variants)
            || ! array_key_exists('file_name', $this->variants[ $variant ])
        ) {
            return false;
        }

        return $this->variants[ $variant ]['file_name'];
    }

    /**
     * {@inheritDoc}
     */
    public function variantExtension(string $variant): string|false
    {
        if (
            ! array_key_exists($variant, $this->variants)
            || ! array_key_exists('extension', $this->variants[ $variant ])
        ) {
            return false;
        }

        return $this->variants[ $variant ]['extension'];
    }

    /**
     * {@inheritDoc}
     */
    public function variantContentType(string $variant): string|false
    {
        if (
            ! array_key_exists($variant, $this->variants)
            || ! array_key_exists('content_type', $this->variants[ $variant ])
        ) {
            return false;
        }

        return $this->variants[ $variant ]['content_type'];
    }

    /**
     * {@inheritDoc}
     */
    public function getInstanceKey(): mixed
    {
        return $this->instanceKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getInstanceClass(): string
    {
        return $this->instanceClass;
    }
}
