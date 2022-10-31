<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class VariantList
{
    /**
     * Normalized array configurations.
     *
     * @var array<string, array<string, mixed>> by variant name
     */
    protected array $variants = [];

    /**
     * @var array<string, string|null> by variant name
     */
    protected array $extensions = [];

    /**
     * @var array<string, string|null> by variant name
     */
    protected array $urls = [];

    /**
     * List of variants to exclude for further merges.
     *
     * @var array<string, bool> by variant name
     */
    protected array $exclude = [];

    /**
     * @param array<string, mixed> $variants
     */
    public function __construct(array $variants)
    {
        $this->extractToArray($variants);
    }


    /**
     * @param array<string, mixed> $variants
     */
    public function mergeDefault(array $variants): void
    {
        $this->extractToArray($variants);
    }

    /**
     * @return array<string, array<string, mixed>> by variant name
     */
    public function variants(): array
    {
        return $this->variants;
    }

    /**
     * @return array<string, string|null> by variant name
     */
    public function extensions(): array
    {
        return $this->extensions;
    }

    /**
     * @return array<string, string|null> by variant name
     */
    public function urls(): array
    {
        return $this->urls;
    }


    /**
     * @param array<string, mixed> $variants
     */
    protected function extractToArray(array $variants): void
    {
        foreach ($variants as $variantName => $options) {
            // If a variant is configured specifically to be excluded, this should override any defaults.
            if ($options === false) {
                $this->markExcluded($variantName);
                continue;
            }

            if ($options instanceof Variant) {
                $variantName = $options->getName();
            }

            // If the variant name is already set, don't overwrite anything.
            if (! $this->shouldMerge($variantName)) {
                continue;
            }

            if ($options instanceof Variant) {
                if ($options->getExtension()) {
                    $this->extensions[ $variantName ] = $options->getExtension();
                }

                if ($options->getUrl()) {
                    $this->urls[ $variantName ] = $options->getUrl();
                }

                $options = $options->getSteps();
            }

            $this->variants[ $variantName ] = $this->normalizeVariantConfigEntry($options);
        }
    }

    /**
     * @param mixed $options
     * @return array<string, mixed>
     */
    protected function normalizeVariantConfigEntry(mixed $options): array
    {
        // Assume dimensions if a string (with dimensions).
        if (is_string($options)) {
            $options = [
                'resize' => [
                    'dimensions' => $options,
                ],
            ];
        }

        // Convert objects to arrays for fluent syntax support.
        if ($options instanceof Arrayable) {
            $options = [$options];
        }

        if (array_key_exists('dimensions', $options)) {
            $options = [
                'resize' => $options,
            ];
        }

        // If auto-orient is set, extract it to its own step.
        if (
            (
                Arr::get($options, 'resize.auto-orient')
                || Arr::get($options, 'resize.auto_orient')
            )
            && ! Arr::has($options, 'auto-orient')
        ) {
            $options = array_merge(['auto-orient' => []], $options);

            Arr::forget($options, [
                'resize.auto-orient',
                'resize.auto_orient',
            ]);
        }

        // Convert to array for fluent syntax support.
        $converted = [];

        foreach ($options as $key => $value) {
            if ($value instanceof Arrayable) {
                foreach ($value->toArray() as $nestedKey => $nestedValue) {
                    $converted[ $nestedKey ] = $nestedValue;
                }
                continue;
            }

            $converted[ $key ] = $value;
        }

        return $converted;
    }

    /**
     * Returns whether variant configuration should be merged in for a given variant name.
     *
     * @param string $variantName
     * @return bool
     */
    protected function shouldMerge(string $variantName): bool
    {
        return ! Arr::has($this->variants, $variantName)
            && ! Arr::get($this->exclude, $variantName);
    }

    /**
     * Mark variant name to be excluded from any further merges.
     *
     * @param string $variantName
     */
    protected function markExcluded(string $variantName): void
    {
        $this->exclude[ $variantName ] = true;
    }
}
