<?php

namespace Czim\Paperclip\Config\Steps;

use BadMethodCallException;

class ResizeStep extends VariantStep
{

    /**
     * @var string
     */
    protected $defaultName = 'resize';

    /**
     * @var null|int
     */
    protected $width;

    /**
     * @var null|int
     */
    protected $height;

    /**
     * @var bool
     */
    protected $crop = false;

    /**
     * @var bool
     */
    protected $ignoreRatio = false;

    /**
     * @var array
     */
    protected $convertOptions = [];


    /**
     * @param int $pixels
     * @return $this
     */
    public function width($pixels)
    {
        $this->width = $pixels;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $pixels
     * @return $this
     */
    public function height($pixels)
    {
        $this->height = $pixels;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $pixels
     * @return $this
     */
    public function square($pixels)
    {
        $this->width = $this->height = $pixels;

        return $this;
    }

    /**
     * @return $this
     */
    public function crop()
    {
        $this->crop = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function ignoreRatio()
    {
        $this->ignoreRatio = true;

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function convertOptions(array $options)
    {
        $this->convertOptions = $options;

        return $this;
    }

    /**
     * @return array
     */
    protected function getStepOptionArray()
    {
        return [
            'dimensions'     => $this->compileDimensionsString(),
            'convertOptions' => $this->convertOptions,
        ];
    }


    /**
     * @return string
     */
    protected function compileDimensionsString()
    {
        // If neither width nor height are set, the configuration is incomplete.
        if ( ! $this->width && ! $this->height) {
            throw new BadMethodCallException('Either width or height must be set');
        }

        // If width or height is not set, the crop or ignore-ratio option are not available.
        if (    ! ($this->width && $this->height)
            &&  ($this->crop || $this->ignoreRatio)
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

        return ($this->width ? $this->width : '')
             . 'x'
             . ($this->height ? $this->height : '')
             . ($this->crop ? '#' : '')
             . ($this->ignoreRatio ? '!' : '');
    }
}
