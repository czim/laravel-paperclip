<?php

namespace Czim\Paperclip\Config\Steps;

use Illuminate\Contracts\Support\Arrayable;

class VariantStep implements Arrayable
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $defaultName = 'variant';


    public function __construct($name = null)
    {
        $this->name = $name ?: $this->defaultName;
    }

    /**
     * @param string $name
     * @return $this|static
     */
    public static function make($name = null)
    {
        return new static($name);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            $this->name => $this->getStepOptionArray(),
        ];
    }

    /**
     * @return array
     * @codeCoverageIgnore
     */
    protected function getStepOptionArray()
    {
        return [];
    }
}
