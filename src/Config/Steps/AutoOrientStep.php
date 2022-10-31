<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config\Steps;

class AutoOrientStep extends VariantStep
{
    protected string $defaultName = 'auto-orient';
    protected bool $quiet = false;


    /**
     * @return $this
     */
    public function quiet(): static
    {
        $this->quiet = true;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStepOptionArray(): array
    {
        return [
            'quiet' => $this->quiet,
        ];
    }
}
