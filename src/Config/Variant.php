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
     * @var string|null
     */
    protected $extension;


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

}
