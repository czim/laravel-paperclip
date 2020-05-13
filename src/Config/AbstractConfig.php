<?php

namespace Czim\Paperclip\Config;

use Czim\FileHandling\Handler\FileHandler;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Illuminate\Support\Arr;

abstract class AbstractConfig implements ConfigInterface
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $normalizedConfig;


    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config           = $config;
        $this->normalizedConfig = $this->normalizeConfig($config);
    }


    /**
     * @return bool
     */
    public function keepOldFiles()
    {
        return (bool) $this->getConfigValue('keep-old-files', false);
    }

    /**
     * @return bool
     */
    public function preserveFiles()
    {
        return (bool) $this->getConfigValue('preserve-files', false);
    }

    /**
     * @return string
     */
    public function storageDisk()
    {
        return $this->getConfigValue('storage');
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->getConfigValue('path');
    }

    /**
     * @return string
     */
    public function variantPath()
    {
        return $this->getConfigValue('variant-path');
    }

    /**
     * @return string
     */
    public function sizeAttribute()
    {
        return $this->getConfigValue('attributes.size');
    }

    /**
     * @return string
     */
    public function contentTypeAttribute()
    {
        return $this->getConfigValue('attributes.content_type');
    }

    /**
     * @return string
     */
    public function updatedAtAttribute()
    {
        return $this->getConfigValue('attributes.updated_at');
    }

    /**
     * @return string
     */
    public function createdAtAttribute()
    {
        return $this->getConfigValue('attributes.created_at');
    }

    /**
     * @return string
     */
    public function variantsAttribute()
    {
        return $this->getConfigValue('attributes.variants');
    }

    /**
     * Returns whether a configuration for a specific variant has been set.
     *
     * @param string $variant
     * @return bool
     */
    public function hasVariantConfig($variant)
    {
        return Arr::has($this->variantConfigs(), $variant);
    }

    /**
     * Returns the configuration array for a specific variant.
     *
     * @param string $variant
     * @return array
     */
    public function variantConfig($variant)
    {
        return Arr::get($this->variantConfigs(), $variant, []);
    }

    /**
     * Returns an array with the variant configurations set.
     *
     * @return array    associative, keyed by variant name
     */
    public function variantConfigs()
    {
        return Arr::get($this->normalizedConfig, FileHandler::CONFIG_VARIANTS);
    }

    /**
     * Returns the mimetype specifically configured for a given variant.
     *
     * @param string $variant
     * @return false|string
     */
    public function variantMimeType($variant)
    {
        return $this->getConfigValue("types.{$variant}") ?: false;
    }

    /**
     * Returns an array with extensions configured per variant.
     *
     * @return string[]     associative, keyed by variant name
     */
    public function variantExtensions()
    {
        return $this->getConfigValue('extensions', []);
    }

    /**
     * Returns the extension specifically configured for a given variant.
     *
     * Note that this does not determine the extension for the variant,
     * just what extension Paperclip should expect the created file to have.
     *
     * @param string $variant
     * @return false|string
     */
    public function variantExtension($variant)
    {
        return $this->getConfigValue("extensions.{$variant}") ?: false;
    }

    /**
     * Returns the default URL to use when no attachment is stored.
     *
     * @return string|null
     */
    public function defaultUrl()
    {
        $defaultVariant = $this->getDefaultUrlVariantName();

        return $this->getConfigValue('url', $this->defaultVariantUrl($defaultVariant));
    }

    /**
     * Returns the default URL for a variant to use when no attachment is stored.
     *
     * @param string $variant
     * @return string|null
     */
    public function defaultVariantUrl($variant)
    {
        $defaultVariant = $this->getDefaultUrlVariantName();

        return $this->getConfigValue(
            "urls.{$variant}",
            $variant === $defaultVariant ? $this->getDefaultUrlWithoutVariantFallback() : null
        );
    }

    /**
     * Returns whether a given attribute property should be saved.
     *
     * Expected values: 'created_at', 'content_type', ...
     *
     * @param string $attribute
     * @return bool
     */
    public function attributeProperty($attribute)
    {
        return $this->getConfigValue("attributes.{$attribute}", true);
    }

    /**
     * Returns the hook callable to run before storing an attachment.
     *
     * @return callable|null
     */
    public function beforeCallable()
    {
        return $this->getConfigValue('before');
    }

    /**
     * Returns the hook callable to run after storing an attachment.
     *
     * @return callable|null
     */
    public function afterCallable()
    {
        return $this->getConfigValue('after');
    }

    /**
     * @return array
     */
    public function getOriginalConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->normalizedConfig;
    }


    /**
     * Takes the set config and creates a normalized version.
     *
     * @param array $config
     * @return array
     */
    abstract protected function normalizeConfig(array $config);


    /**
     * Returns the config value relevant for this attachment.
     *
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function getConfigValue($key, $default = null)
    {
        if (Arr::has($this->normalizedConfig, $key)) {
            return Arr::get($this->normalizedConfig, $key);
        }

        return $this->getFallbackConfigValue($key, $default);
    }

    /**
     * Returns the global configuration fallback for a config value.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function getFallbackConfigValue($key, $default = null)
    {
        $map = [
            'keep-old-files' => 'keep-old-files',
            'preserve-files' => 'preserve-files',
            'storage'        => 'storage.disk',
            'path'           => 'path.original',
            'variant-path'   => 'path.variant',

            'attributes.size'         => 'model.attributes.size',
            'attributes.content_type' => 'model.attributes.content_type',
            'attributes.updated_at'   => 'model.attributes.updated_at',
            'attributes.created_at'   => 'model.attributes.created_at',
            'attributes.variants'     => 'model.attributes.variants',
        ];

        if ( ! in_array($key, array_keys($map))) {
            return $default;
        }

        return config('paperclip.' . $map[ $key ], $default);
    }

    /**
     * @return string|null
     */
    protected function getDefaultUrlWithoutVariantFallback()
    {
        return $this->getConfigValue('url');
    }

    /**
     * Returns the variant name to consider default ('original') for URL generation.
     *
     * @return string
     */
    protected function getDefaultUrlVariantName()
    {
        return config('paperclip.default-variant', 'original');
    }
}
