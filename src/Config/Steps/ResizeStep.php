<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config\Steps;

use BadMethodCallException;

class ResizeStep extends VariantStep
{
    protected string $defaultName = 'resize';

    protected ?int $width = null;
    protected ?int $height = null;
    protected bool $crop = false;
    protected bool $ignoreRatio = false;

    /**
     * @var array<string, mixed>
     */
    protected array $convertOptions = [];


    /**
     * @param int $pixels
     * @return $this
     */
    public function width(int $pixels): static
    {
        $this->width = $pixels;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int $pixels
     * @return $this
     */
    public function height(int $pixels): static
    {
        $this->height = $pixels;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int $pixels
     * @return $this
     */
    public function square(int $pixels): static
    {
        $this->width = $this->height = $pixels;

        return $this;
    }

    /**
     * @return $this
     */
    public function crop(): static
    {
        $this->crop = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function ignoreRatio(): static
    {
        $this->ignoreRatio = true;

        return $this;
    }

    /**
     * @param array<string, mixed> $options
     * @return $this
     */
    public function convertOptions(array $options): static
    {
        $this->convertOptions = $options;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getStepOptionArray(): array
    {
        return [
            'dimensions'     => $this->compileDimensionsString(),
            'convertOptions' => $this->convertOptions,
        ];
    }


    protected function compileDimensionsString(): string
    {
        // If neither width nor height are set, the configuration is incomplete.
        if (! $this->width && ! $this->height) {
            throw new BadMethodCallException('Either width or height must be set');
        }

        // If width or height is not set, the crop or ignore-ratio option are not available.
        if (
            ! ($this->width && $this->height)
            && ($this->crop || $this->ignoreRatio)
        ) {
            throw new BadMethodCallException(
                "Cannot use 'crop' or 'ignoreRatio' unless both width and height are set"
            );
        }

        // Crop and ignore-ratio conflict.
        if ($this->crop && $this->ignoreRatio) {
            throw new BadMethodCallException(
                "Only one of 'crop' and 'ignoreRatio' can be used"
            );
        }

        return ($this->width ?: '')
            . 'x'
            . ($this->height ?: '')
            . ($this->crop ? '#' : '')
            . ($this->ignoreRatio ? '!' : '');
    }
}
