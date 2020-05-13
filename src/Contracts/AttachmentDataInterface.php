<?php

namespace Czim\Paperclip\Contracts;

interface AttachmentDataInterface
{

    /**
     * Returns the name (the attribute on the model) for the attachment.
     *
     * @return string
     */
    public function name();

    /**
     * Returns the configuration.
     *
     * @return array
     */
    public function getConfig();

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
     * Returns the mimeType for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantContentType($variant);

    /**
     * Returns the JSON information stored on the model about variants as an associative array.
     *
     * @return array
     */
    public function variantsAttribute();

    /**
     * Returns the key for the underlying object instance.
     *
     * @return mixed
     */
    public function getInstanceKey();

    /**
     * Returns the class type of the attachment's underlying object instance.
     *
     * @return string
     */
    public function getInstanceClass();
}
