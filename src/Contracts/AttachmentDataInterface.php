<?php

namespace Czim\Paperclip\Contracts;

use Illuminate\Database\Eloquent\Model;

interface AttachmentDataInterface
{
    public function name(): string;

    /**
     * Returns the configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * Returns the creation time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_created_at attribute of the model.
     *
     * This attribute may conditionally exist on the model, it is not one of the four required fields.
     *
     * @return string|null
     */
    public function createdAt(): ?string;

    /**
     * Returns the last modified time of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_updated_at attribute of the model.
     *
     * @return string|null
     */
    public function updatedAt(): ?string;

    /**
     * Returns the content type of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_content_type attribute of the model.
     *
     * @return string|null
     */
    public function contentType(): ?string;

    /**
     * Returns the size of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_size attribute of the model.
     *
     * @return int|null
     */
    public function size(): ?int;

    /**
     * Returns the name of the file as originally assigned to this attachment's model.
     * Lives in the <attachment>_file_name attribute of the model.
     *
     * @return string|null
     */
    public function originalFilename(): ?string;

    /**
     * Returns the filename for a given variant.
     *
     * @param string|null $variant
     * @return string|false
     */
    public function variantFilename(?string $variant): string|false;

    /**
     * Returns the extension for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantExtension(string $variant): string|false;

    /**
     * Returns the mimeType for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantContentType(string $variant): string|false;

    /**
     * Returns the JSON information stored on the model about variants as an associative array.
     *
     * @return array<string, mixed>
     */
    public function variantsAttribute(): array;

    /**
     * Returns the key for the underlying object instance.
     *
     * @return mixed
     */
    public function getInstanceKey(): mixed;

    /**
     * Returns the class type of the attachment's underlying object instance.
     *
     * @return class-string<AttachableInterface&Model>
     */
    public function getInstanceClass(): string;
}
