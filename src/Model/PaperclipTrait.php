<?php

declare(strict_types=1);

namespace Czim\Paperclip\Model;

use Czim\FileHandling\Contracts\Storage\StorableFileFactoryInterface;
use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentFactoryInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements AttachableInterface
 * @mixin Model
 */
trait PaperclipTrait
{
    /**
     * All of the model's current attached files.
     *
     * @var AttachmentInterface[]
     */
    protected array $attachedFiles = [];

    /**
     * Whether an attachment has been updated and requires further processing.
     *
     * @var bool
     */
    protected bool $attachedUpdated = false;


    /**
     * Returns the list of attached files.
     *
     * @return AttachmentInterface[]
     */
    public function getAttachedFiles(): array
    {
        return $this->attachedFiles;
    }

    /**
     * Add a new file attachment type to the list of available attachments.
     *
     * @param string               $name
     * @param array<string, mixed> $options
     */
    public function hasAttachedFile(string $name, array $options = []): void
    {
        $factory = $this->makeAttachmentFactory();

        $attachment = $factory->create($this, $name, $options);

        $this->attachedFiles[ $name ] = $attachment;
    }

    public static function bootPaperclipTrait(): void
    {
        static::saved(function ($model): void {
            /** @var static&AttachableInterface $model */
            if ($model->attachedUpdated) {
                // Unmark attachment being updated, so the processing isn't fired twice
                // when the attached file performs a model update for the `variants` column.
                $model->attachedUpdated = false;

                foreach ($model->getAttachedFiles() as $attachedFile) {
                    $attachedFile->afterSave($model);
                }

                $model->mergeFileAttributes();
            }
        });

        static::saving(function ($model): void {
            /** @var static $model */
            $model->removeFileAttributes();
        });

        static::updating(function ($model): void {
            /** @var static $model */
            $model->removeFileAttributes();
        });

        static::retrieved(function ($model): void {
            /** @var static $model */
            $model->mergeFileAttributes();
        });

        static::deleting(function ($model): void {
            /** @var static&AttachableInterface $model */
            foreach ($model->getAttachedFiles() as $attachedFile) {
                $attachedFile->beforeDelete($model);
            }
        });

        static::deleted(function ($model): void {
            /** @var Model&AttachableInterface $model */
            foreach ($model->getAttachedFiles() as $attachedFile) {
                $attachedFile->afterDelete($model);
            }
        });
    }

    /**
     * Handle the dynamic retrieval of attachment objects.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute(mixed $key): mixed
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            return $this->attachedFiles[ $key ];
        }

        return parent::getAttribute($key);
    }

    /**
     * Handles the setting of attachment objects.
     *
     * @param string $key
     * @param mixed  $value
     * @return mixed|$this
     */
    public function setAttribute(mixed $key, mixed $value): mixed
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            if ($value) {
                $attachedFile = $this->attachedFiles[ $key ];

                if ($value === $this->getDeleteAttachmentString()) {
                    $attachedFile->setToBeDeleted();
                    return $this;
                }

                $factory = $this->makeStorableFileFactory();

                $attachedFile->setUploadedFile(
                    $factory->makeFromAny($value)
                );
            }

            $this->attachedUpdated = true;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Overridden to prevent attempts to persist attachment attributes directly.
     *
     * Reason this is required: Laravel 5.5 changed the getDirty() behavior.
     *
     * {@inheritDoc}
     */
    public function originalIsEquivalent($key)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            return true;
        }

        return parent::originalIsEquivalent($key);
    }

    /**
     * Return the image paths for a given attachment.
     *
     * @param string $attachmentName
     * @return string[]
     */
    public function pathsForAttachment(string $attachmentName): array
    {
        $paths = [];

        if (isset($this->attachedFiles[ $attachmentName ])) {
            $attachment = $this->attachedFiles[ $attachmentName ];

            foreach ($attachment->variants(true) as $variant) {
                $paths[ $variant ] = $attachment->variantPath($variant);
            }
        }

        return $paths;
    }

    /**
     * Return the image urls for a given attachment.
     *
     * @param string $attachmentName
     * @return string[]
     */
    public function urlsForAttachment(string $attachmentName): array
    {
        $urls = [];

        if (isset($this->attachedFiles[ $attachmentName ])) {
            $attachment = $this->attachedFiles[ $attachmentName ];

            foreach ($attachment->variants(true) as $variant) {
                $urls[ $variant ] = $attachment->url($variant);
            }
        }

        return $urls;
    }

    /**
     * Marks that at least one attachment on the model has been updated and should be processed.
     */
    public function markAttachmentUpdated(): void
    {
        $this->attachedUpdated = true;
    }

    /**
     * Add the attached files to the model's attributes.
     */
    public function mergeFileAttributes(): void
    {
        $this->attributes = $this->attributes + $this->getAttachedFiles();
    }

    /**
     * Remove any attached file attributes, so they aren't sent to the database.
     */
    public function removeFileAttributes(): void
    {
        foreach (array_keys($this->getAttachedFiles()) as $key) {
            unset($this->attributes[$key]);
        }
    }

    /**
     * Returns the string with which an attachment can be deleted.
     *
     * @return string
     */
    protected function getDeleteAttachmentString(): string
    {
        return config('paperclip.delete-hash', Attachment::NULL_ATTACHMENT);
    }

    protected function makeAttachmentFactory(): AttachmentFactoryInterface
    {
        return app(AttachmentFactoryInterface::class);
    }

    protected function makeStorableFileFactory(): StorableFileFactoryInterface
    {
        return app(StorableFileFactoryInterface::class);
    }
}
