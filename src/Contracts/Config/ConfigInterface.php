<?php

namespace Czim\Paperclip\Contracts\Config;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @extends Arrayable<string, mixed>
 */
interface ConfigInterface extends Arrayable
{
    public function keepOldFiles(): bool;
    public function preserveFiles(): bool;
    public function storageDisk(): ?string;
    public function path(): string;
    public function variantPath(): ?string;
    public function sizeAttribute(): string|bool;
    public function contentTypeAttribute(): string|bool;
    public function updatedAtAttribute(): string|bool;
    public function createdAtAttribute(): string|bool;
    public function variantsAttribute(): string|bool;

    /**
     * Returns whether a configuration for a specific variant has been set.
     *
     * @param string $variant
     * @return bool
     */
    public function hasVariantConfig(string $variant): bool;

    /**
     * Returns the configuration array for a specific variant.
     *
     * @param string $variant
     * @return array<string, mixed>
     */
    public function variantConfig(string $variant): array;

    /**
     * Returns an array with the variant configurations set.
     *
     * @return array<string, array<string, mixed>> keyed by variant name
     */
    public function variantConfigs(): array;

    /**
     * Returns the mimetype specifically configured for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantMimeType(string $variant): string|false;

    /**
     * Returns an array with extensions configured per variant.
     *
     * @return array<string, string|null> keyed by variant name
     */
    public function variantExtensions(): array;

    /**
     * Returns the extension specifically configured for a given variant.
     *
     * Note that this does not determine the extension for the variant,
     * just what extension Paperclip should expect the created file to have.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantExtension(string $variant): string|false;

    /**
     * Returns the default URL to use when no attachment is stored.
     *
     * @return string|null
     */
    public function defaultUrl(): ?string;

    /**
     * Returns the default URL for a variant to use when no attachment is stored.
     *
     * @param string $variant
     * @return string|null
     */
    public function defaultVariantUrl(string $variant): ?string;

    /**
     * Returns whether a given attribute property should be saved.
     *
     * Expected values: 'created_at', 'content_type', ...
     *
     * @param string $attribute
     * @return bool
     */
    public function attributeProperty(string $attribute): bool;

    /**
     * Returns the hook callable to run before storing an attachment.
     *
     * @return callable|callable-string|null
     */
    public function beforeCallable(): callable|string|null;

    /**
     * Returns the hook callable to run after storing an attachment.
     *
     * @return callable|callable-string|null
     */
    public function afterCallable(): callable|string|null;

    /**
     * Returns the unedited, non-normalized input config array.
     *
     * @return array<string, mixed>
     */
    public function getOriginalConfig(): array;
}
