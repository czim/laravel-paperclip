<?php

namespace Czim\Paperclip\Contracts\Config;

use Illuminate\Contracts\Support\Arrayable;

interface ConfigInterface extends Arrayable
{

    /**
     * @return bool
     */
    public function keepOldFiles();

    /**
     * @return bool
     */
    public function preserveFiles();

    /**
     * @return string
     */
    public function storageDisk();

    /**
     * @return string
     */
    public function path();

    /**
     * @return string
     */
    public function variantPath();

    /**
     * @return string
     */
    public function sizeAttribute();

    /**
     * @return string
     */
    public function contentTypeAttribute();

    /**
     * @return string
     */
    public function updatedAtAttribute();

    /**
     * @return string
     */
    public function createdAtAttribute();

    /**
     * @return string
     */
    public function variantsAttribute();

    /**
     * Returns whether a configuration for a specific variant has been set.
     *
     * @param string $variant
     * @return bool
     */
    public function hasVariantConfig($variant);

    /**
     * Returns the configuration array for a specific variant.
     *
     * @param string $variant
     * @return array
     */
    public function variantConfig($variant);

    /**
     * Returns an array with the variant configurations set.
     *
     * @return array    associative, keyed by variant name
     */
    public function variantConfigs();

    /**
     * Returns the mimetype specifically configured for a given variant.
     *
     * @param string $variant
     * @return false|string
     */
    public function variantMimeType($variant);

    /**
     * Returns an array with extensions configured per variant.
     *
     * @return string[]     associative, keyed by variant name
     */
    public function variantExtensions();

    /**
     * Returns the extension specifically configured for a given variant.
     *
     * Note that this does not determine the extension for the variant,
     * just what extension Paperclip should expect the created file to have.
     *
     * @param string $variant
     * @return false|string
     */
    public function variantExtension($variant);

    /**
     * Returns the default URL to use when no attachment is stored.
     *
     * @return string|null
     */
    public function defaultUrl();

    /**
     * Returns the default URL for a variant to use when no attachment is stored.
     *
     * @param string $variant
     * @return string|null
     */
    public function defaultVariantUrl($variant);

    /**
     * Returns whether a given attribute property should be saved.
     *
     * Expected values: 'created_at', 'content_type', ...
     *
     * @param string $attribute
     * @return bool
     */
    public function attributeProperty($attribute);

    /**
     * Returns the hook callable to run before storing an attachment.
     *
     * @return callable|null
     */
    public function beforeCallable();

    /**
     * Returns the hook callable to run after storing an attachment.
     *
     * @return callable|null
     */
    public function afterCallable();

    /**
     * Returns the unedited, non-normalized input config array.
     *
     * @return array
     */
    public function getOriginalConfig();
}
