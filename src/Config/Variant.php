<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config;

use Czim\Paperclip\Config\Steps\VariantStep;

/**
 * @phpstan-consistent-constructor
 */
class Variant
{
    /**
     * Variant processing steps.
     *
     * @var array<int|string, mixed>
     */
    protected array $steps = [];

    /**
     * The extension that the variant's file is expected to be stored with.
     *
     * @var string|null
     */
    protected ?string $extension = null;

    /**
     * Fallback-URL to use when no attachment is stored.
     *
     * @var string|null
     */
    protected ?string $url = null;


    public function __construct(protected readonly string $name)
    {
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * Sets variant processing steps.
     *
     * @param array<int|string, mixed>|string|VariantStep $steps
     * @return $this
     */
    public function steps(array|string|VariantStep $steps): static
    {
        if (! is_array($steps)) {
            $steps = [ $steps ];
        }

        $this->steps = $steps;

        return $this;
    }

    /**
     * The filename extension to use.
     *
     * @param string|null $extension
     * @return $this
     */
    public function extension(?string $extension): static
    {
        if (is_string($extension)) {
            $this->extension = ltrim($extension, '.');
        } else {
            $this->extension = null;
        }

        return $this;
    }

    /**
     * Sets the fallback URL to use when the attachment is not stored.
     *
     * @param string $url
     * @return $this
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}
