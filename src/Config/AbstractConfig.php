<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config;

use Czim\FileHandling\Handler\FileHandler;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Illuminate\Support\Arr;

abstract class AbstractConfig implements ConfigInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $normalizedConfig;


    /**
     * @param array<string, mixed> $config
     */
    public function __construct(protected array $config)
    {
        $this->normalizedConfig = $this->normalizeConfig($config);
    }


    public function keepOldFiles(): bool
    {
        return (bool) $this->getConfigValue('keep-old-files', false);
    }

    public function preserveFiles(): bool
    {
        return (bool) $this->getConfigValue('preserve-files', false);
    }

    public function storageDisk(): ?string
    {
        return $this->getConfigValue('storage');
    }

    public function path(): string
    {
        return $this->getConfigValue('path');
    }

    public function variantPath(): ?string
    {
        return $this->getConfigValue('variant-path');
    }

    public function sizeAttribute(): string|bool
    {
        return $this->getConfigValue('attributes.size');
    }

    public function contentTypeAttribute(): string|bool
    {
        return $this->getConfigValue('attributes.content_type');
    }

    public function updatedAtAttribute(): string|bool
    {
        return $this->getConfigValue('attributes.updated_at');
    }

    public function createdAtAttribute(): string|bool
    {
        return $this->getConfigValue('attributes.created_at');
    }

    public function variantsAttribute(): string|bool
    {
        return $this->getConfigValue('attributes.variants');
    }

    /**
     * Returns whether a configuration for a specific variant has been set.
     *
     * @param string $variant
     * @return bool
     */
    public function hasVariantConfig(string $variant): bool
    {
        return Arr::has($this->variantConfigs(), $variant);
    }

    /**
     * Returns the configuration array for a specific variant.
     *
     * @param string $variant
     * @return array<string, mixed>
     */
    public function variantConfig(string $variant): array
    {
        return Arr::get($this->variantConfigs(), $variant, []);
    }

    /**
     * Returns an array with the variant configurations set.
     *
     * @return array<string, array<string, mixed>> keyed by variant name
     */
    public function variantConfigs(): array
    {
        return Arr::get($this->normalizedConfig, FileHandler::CONFIG_VARIANTS);
    }

    /**
     * Returns the mimetype specifically configured for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantMimeType(string $variant): string|false
    {
        return $this->getConfigValue("types.{$variant}") ?: false;
    }

    /**
     * Returns an array with extensions configured per variant.
     *
     * @return array<string, string|null> keyed by variant name
     */
    public function variantExtensions(): array
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
     * @return string|false
     */
    public function variantExtension(string $variant): string|false
    {
        return $this->getConfigValue("extensions.{$variant}") ?: false;
    }

    /**
     * Returns the default URL to use when no attachment is stored.
     *
     * @return string|null
     */
    public function defaultUrl(): ?string
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
    public function defaultVariantUrl(string $variant): ?string
    {
        $defaultVariant = $this->getDefaultUrlVariantName();

        return $this->getConfigValue(
            "urls.{$variant}",
            $variant === $defaultVariant
                ? $this->getDefaultUrlWithoutVariantFallback()
                : null
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
    public function attributeProperty(string $attribute): bool
    {
        return (bool) $this->getConfigValue("attributes.{$attribute}", true);
    }

    /**
     * Returns the hook callable to run before storing an attachment.
     *
     * @return callable|callable-string|null
     */
    public function beforeCallable(): callable|string|null
    {
        return $this->getConfigValue('before');
    }

    /**
     * Returns the hook callable to run after storing an attachment.
     *
     * @return callable|callable-string|null
     */
    public function afterCallable(): callable|string|null
    {
        return $this->getConfigValue('after');
    }

    /**
     * @return array<string, mixed>
     */
    public function getOriginalConfig(): array
    {
        return $this->config;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->normalizedConfig;
    }


    /**
     * Takes the set config and creates a normalized version.
     *
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    abstract protected function normalizeConfig(array $config): array;


    /**
     * Returns the config value relevant for this attachment.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function getConfigValue(string $key, mixed $default = null): mixed
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
    protected function getFallbackConfigValue(string $key, mixed $default = null): mixed
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

        if (! in_array($key, array_keys($map))) {
            return $default;
        }

        return config('paperclip.' . $map[ $key ], $default);
    }

    protected function getDefaultUrlWithoutVariantFallback(): ?string
    {
        return $this->getConfigValue('url');
    }

    /**
     * Returns the variant name to consider default ('original') for URL generation.
     *
     * @return string
     */
    protected function getDefaultUrlVariantName(): string
    {
        return config('paperclip.default-variant', 'original');
    }
}
