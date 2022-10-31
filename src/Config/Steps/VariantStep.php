<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config\Steps;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @phpstan-consistent-constructor
 */
class VariantStep implements Arrayable
{
    protected string $name;
    protected string $defaultName = 'variant';


    public function __construct(?string $name = null)
    {
        $this->name = $name ?: $this->defaultName;
    }

    /**
     * @param string|null $name
     * @return static
     */
    public static function make(?string $name = null): static
    {
        return new static($name);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            $this->name => $this->getStepOptionArray(),
        ];
    }

    /**
     * @return array<string, mixed>
     * @codeCoverageIgnore
     */
    protected function getStepOptionArray(): array
    {
        return [];
    }
}
