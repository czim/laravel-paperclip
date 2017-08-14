<?php
namespace Czim\Paperclip\Contracts;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;

interface AttachmentInterface
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
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config);

    /**
     * Returns the configuration.
     *
     * @return array
     */
    public function getConfig();

    /**
     * Sets the name (the attribute on the model) for the attachment.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Returns the name (the attribute on the model) for the attachment.
     *
     * @return string
     */
    public function name();

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
     * Returns list of keys for defined variants.
     *
     * @return string[]
     */
    public function variants();

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
     * Returns the creation time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_created_at attribute of the model.
     * This attribute may conditionally exist on the model, it is not one of the four required fields.
     *
     * @return string
     */
    public function createdAt();

    /**
     * Returns the last modified time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_updated_at attribute of the model.
     *
     * @return string
     */
    public function updatedAt();

    /**
     * Returns the content type of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_content_type attribute of the model.
     *
     * @return string
     */
    public function contentType();

    /**
     * Returns the size of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_size attribute of the model.
     *
     * @return int
     */
    public function size();

    /**
     * Returns the name of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_name attribute of the model.
     *
     * @return string
     */
    public function originalFilename();

    /**
     * Returns the filename for a given variant.
     *
     * @param string|null $variant
     * @return string
     */
    public function variantFilename($variant);

    /**
     * Returns the extension for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantExtension($variant);

    /**
     * Returns the class type of the attachment's underlying object instance.
     *
     * @return string
     */
    public function getInstanceClass();

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
