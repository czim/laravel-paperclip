<?php

namespace Czim\Paperclip\Config\Steps;

class WatermarkStep extends VariantStep
{
    protected string $defaultName = 'watermark';

    protected string $path;
    protected string $position;

    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function position(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    protected function getStepOptionArray(): array
    {
        return [
            'watermark' => $this->path,
            'position' => $this->position,
        ];
    }
}
