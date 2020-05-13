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
     * @return $this
     */
    public function setInstance(AttachableInterface $instance);

    /**
     * Returns the underlying instance (model) object for this attachment.
     *
     * @return AttachableInterface
     */
    public function getInstance();

    /**
     * Sets the configuration.
     *
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config);

    /**
     * Returns the configuration after normalization.
     *
     * @return array
     */
    public function getNormalizedConfig();

    /**
     * Sets the storage disk identifier.
     *
     * @param string $storage   disk identifier
     * @return $this
     */
    public function setStorage($storage);

    /**
     * Returns the storage disk used by the attachment.
     *
     * @return null|string
     */
    public function getStorage();

    /**
     * Sets the name (the attribute on the model) for the attachment.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @param InterpolatorInterface $interpolator
     * @return $this
     */
    public function setInterpolator(InterpolatorInterface $interpolator);

    /**
     * Sets a file to be processed and stored.
     *
     * This is not done instantly. Rather, the file is queued for processing when the model is saved.
     *
     * @param StorableFileInterface $file
     */
    public function setUploadedFile(StorableFileInterface $file);

    /**
     * Sets the attachment to be deleted.
     *
     * This does NOT override the preserve-files config option.
     */
    public function setToBeDeleted();

    /**
     * Reprocesses variants from the currently set original file.
     *
     * @param array $variants   ['*'] for all
     */
    public function reprocess($variants = ['*']);

    /**
     * Returns list of keys for defined variants.
     *
     * @param bool $withOriginal    whether to include the original 'variant' key
     * @return string[]
     */
    public function variants($withOriginal = false);

    /**
     * Generates the url to an uploaded file (or a variant).
     *
     * @param string $variant
     * @return string
     */
    public function url($variant = null);

    /**
     * Returns the relative storage path for a variant.
     *
     * @param string|null $variant
     * @return string
     */
    public function variantPath($variant = null);

    /**
     * Returns whether this attachment actually has a file currently stored.
     *
     * @return bool
     */
    public function exists();

    /**
     * Removes all uploaded files (from storage) for this attachment.
     *
     * This method does not clear out attachment attributes on the model instance.
     *
     * @param array $variants
     */
    public function destroy(array $variants = []);

    /**
     * Processes the write queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterSave(AttachableInterface $instance);

    /**
     * Queues up this attachments files for deletion.
     *
     * @param AttachableInterface $instance
     */
    public function beforeDelete(AttachableInterface $instance);

    /**
     * Processes the delete queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterDelete(AttachableInterface $instance);
}
