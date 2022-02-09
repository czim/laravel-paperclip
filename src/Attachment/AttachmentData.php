<?php

namespace Czim\Paperclip\Attachment;

use Czim\Paperclip\Contracts\AttachmentDataInterface;

/**
 * Data object that reflects a previous state of the attachment.
 * This is used for queued deletion of a previously stored attachment.
 */
class AttachmentData implements AttachmentDataInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $variants;

    /**
     * @var mixed
     */
    protected $instanceKey;

    /**
     * @var string
     */
    protected $instanceClass;



    /**
     * @param string $name
     * @param array  $config
     * @param array  $attributes
     * @param array  $variants
     * @param mixed  $instanceKey
     * @param string $instanceClass
     */
    public function __construct(
        $name,
        array $config,
        array $attributes,
        array $variants,
        $instanceKey,
        $instanceClass
    ) {
        $this->name          = $name;
        $this->config        = $config;
        $this->attributes    = $attributes;
        $this->variants      = $variants;
        $this->instanceKey   = $instanceKey;
        $this->instanceClass = $instanceClass;
    }


    /**
     * Returns the name (the attribute on the model) for the attachment.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Returns the configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the creation time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_created_at attribute of the model.
     * This attribute may conditionally exist on the model, it is not one of the four required fields.
     *
     * @return string|null
     */
    public function createdAt()
    {
        if ( ! array_key_exists('created_at', $this->attributes)) {
            return null;
        }

        return $this->attributes['created_at'];
    }

    /**
     * Returns the last modified time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_updated_at attribute of the model.
     *
     * @return string
     */
    public function updatedAt()
    {
        if ( ! array_key_exists('updated_at', $this->attributes)) {
            return null;
        }

        return $this->attributes['updated_at'];
    }

    /**
     * Returns the content type of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_content_type attribute of the model.
     *
     * @return string
     */
    public function contentType()
    {
        if ( ! array_key_exists('content_type', $this->attributes)) {
            return null;
        }

        return $this->attributes['content_type'];
    }

    /**
     * Returns the size of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_size attribute of the model.
     *
     * @return int
     */
    public function size()
    {
        if ( ! array_key_exists('file_size', $this->attributes)) {
            return null;
        }

        return $this->attributes['file_size'];
    }

    /**
     * Returns the name of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_name attribute of the model.
     *
     * @return string
     */
    public function originalFilename()
    {
        if ( ! array_key_exists('file_name', $this->attributes)) {
            return null;
        }

        return $this->attributes['file_name'];
    }

    /**
     * Returns the JSON information stored on the model about variants as an associative array.
     *
     * @return array
     */
    public function variantsAttribute()
    {
        if ( ! array_key_exists('variants', $this->attributes)) {
            return [];
        }

        return $this->attributes['variants'];
    }

    /**
     * Returns the filename for a given variant.
     *
     * @param string|null $variant
     * @return string
     */
    public function variantFilename($variant)
    {
        if (    ! array_key_exists($variant, $this->variants)
            ||  ! is_array($this->variants[ $variant ])
            ||  ! array_key_exists('file_name', $this->variants[ $variant ])
        ) {
            return false;
        }

        return $this->variants[ $variant ]['file_name'];
    }

    /**
     * Returns the extension for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantExtension($variant)
    {
        if (    ! array_key_exists($variant, $this->variants)
            ||  ! is_array($this->variants[ $variant ])
            ||  ! array_key_exists('extension', $this->variants[ $variant ])
        ) {
            return false;
        }

        return $this->variants[ $variant ]['extension'];
    }

    /**
     * Returns the mimeType for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantContentType($variant)
    {
        if (    ! array_key_exists($variant, $this->variants)
            ||  ! is_array($this->variants[ $variant ])
            ||  ! array_key_exists('content_type', $this->variants[ $variant ])
        ) {
            return false;
        }

        return $this->variants[ $variant ]['content_type'];
    }

    /**
     * Returns the key for the underlying object instance.
     *
     * @return mixed
     */
    public function getInstanceKey()
    {
        return $this->instanceKey;
    }

    /**
     * Returns the class type of the attachment's underlying object instance.
     *
     * @return string
     */
    public function getInstanceClass()
    {
        return $this->instanceClass;
    }
}
