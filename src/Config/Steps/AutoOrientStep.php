<?php

namespace Czim\Paperclip\Config\Steps;

class AutoOrientStep extends VariantStep
{

    /**
     * @var string
     */
    protected $defaultName = 'auto-orient';

    /**
     * @var bool
     */
    protected $quiet = false;


    /**
     * @return $this
     */
    public function quiet()
    {
        $this->quiet = true;

        return $this;
    }

    /**
     * @return array
     */
    protected function getStepOptionArray()
    {
        return [
            'quiet' => $this->quiet,
        ];
    }
}
