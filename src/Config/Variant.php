<?php

namespace Czim\Paperclip\Config;

use Illuminate\Contracts\Support\Arrayable;

class Variant
{

    /**
     * The name/identifier of the variant.
     *
     * @var string
     */
    protected $name;

    /**
     * Variant processing steps.
     *
     * @var array
     */
    protected $steps = [];

    /**
     * The extension that the variant's file is expected to be stored with.
     *
     * @var string|null
     */
    protected $extension;

    /**
     * Fallback-URL to use when no attachment is stored.
     *
     * @var string|null
     */
    protected $url;


    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return $this|static
     */
    public static function make($name)
    {
        return new static($name);
    }

    /**
     * Sets variant processing steps.
     *
     * @param array|string|Arrayable $steps
     * @return $this
     */
    public function steps($steps)
    {
        if ( ! is_array($steps)) {
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
    public function extension($extension)
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
    public function url($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @return null|string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
