<?php

namespace Czim\Paperclip\Contracts;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use JsonSerializable;

interface AttachmentInterface extends AttachmentDataInterface, JsonSerializable
{
    /**
     * Sets the underlying instance object.
     *
     * @param AttachableInterface $instance
     */
    public function setInstance(AttachableInterface $instance): void;

    /**
     * Returns the underlying instance (model) object for this attachment.
     *
     * @return AttachableInterface
     */
    public function getInstance(): AttachableInterface;

    public function setConfig(ConfigInterface $config): void;

    /**
     * Returns the configuration after normalization.
     *
     * @return array<string, mixed>
     */
    public function getNormalizedConfig(): array;

    /**
     * Sets the storage disk identifier.
     *
     * @param string|null $storage
     */
    public function setStorage(?string $storage): void;

    public function getStorage(): ?string;

    public function setName(string $name): void;

    public function setInterpolator(InterpolatorInterface $interpolator): void;

    /**
     * Sets a file to be processed and stored.
     *
     * This is not done instantly. Rather, the file is queued for processing when the model is saved.
     *
     * @param StorableFileInterface $file
     */
    public function setUploadedFile(StorableFileInterface $file): void;

    /**
     * Sets the attachment to be deleted.
     *
     * This does NOT override the preserve-files config option.
     */
    public function setToBeDeleted(): void;

    /**
     * Reprocesses variants from the currently set original file.
     *
     * @param string[] $variants   ['*'] for all
     */
    public function reprocess(array $variants = ['*']);

    /**
     * Returns list of keys for defined variants.
     *
     * @param bool $withOriginal    whether to include the original 'variant' key
     * @return string[]
     */
    public function variants(bool $withOriginal = false): array;

    /**
     * Generates the url to an uploaded file (or a variant).
     *
     * @param string|null $variant
     * @return string|null
     */
    public function url(?string $variant = null): ?string;

    /**
     * Returns the relative storage path for a variant.
     *
     * @param string|null $variant
     * @return string|null
     */
    public function variantPath(?string $variant = null): ?string;

    /**
     * Returns whether this attachment actually has a file currently stored.
     *
     * @return bool
     */
    public function exists(): bool;

    /**
     * Removes all uploaded files (from storage) for this attachment.
     *
     * This method does not clear out attachment attributes on the model instance.
     *
     * @param string[] $variants
     */
    public function destroy(array $variants = []): void;

    /**
     * Processes the write queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterSave(AttachableInterface $instance): void;

    /**
     * Queues up this attachments files for deletion.
     *
     * @param AttachableInterface $instance
     */
    public function beforeDelete(AttachableInterface $instance): void;

    /**
     * Processes the delete queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterDelete(AttachableInterface $instance): void;
}
