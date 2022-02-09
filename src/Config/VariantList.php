<?php

namespace Czim\Paperclip\Config;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class VariantList
{

    /**
     * Normalized array configurations.
     *
     * @var array       associative, by variant name
     */
    protected $variants = [];

    /**
     * @var string[]    associative, by variant name
     */
    protected $extensions = [];

    /**
     * @var string[]    associative, by variant name
     */
    protected $urls = [];

    /**
     * List of variants to exclude for further merges.
     *
     * @var array       associative, by variant name
     */
    protected $exclude = [];


    public function __construct(array $variants)
    {
        $this->extractToArray($variants);
    }


    /**
     * @param array $variants
     * @return $this
     */
    public function mergeDefault(array $variants)
    {
        $this->extractToArray($variants);

        return $this;
    }

    /**
     * @return array
     */
    public function variants()
    {
        return $this->variants;
    }

    /**
     * @return string[]
     */
    public function extensions()
    {
        return $this->extensions;
    }

    /**
     * @return string[]
     */
    public function urls()
    {
        return $this->urls;
    }


    protected function extractToArray(array $variants)
    {
        foreach ($variants as $variantName => $options) {

            // If a variant is configured specifically to be excluded,
            // this should override any defaults
            if ($options === false) {
                $this->markExcluded($variantName);
                continue;
            }

            if ($options instanceof Variant) {
                $variantName = $options->getName();
            }

            // If the variant name is already set, don't overwrite anything
            if ( ! $this->shouldMerge($variantName)) {
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
     * @return array
     */
    protected function normalizeVariantConfigEntry($options)
    {
        // Assume dimensions if a string (with dimensions)
        if (is_string($options)) {
            $options = [
                'resize' => [
                    'dimensions' => $options,
                ],
            ];
        }

        // Convert objects to arrays for fluent syntax support
        if ($options instanceof Arrayable) {
            $options = [ $options ];
        }

        if (array_key_exists('dimensions', $options)) {
            $options = [
                'resize' => $options,
            ];
        }

        // If auto-orient is set, extract it to its own step
        if (    (   Arr::get($options, 'resize.auto-orient')
                ||  Arr::get($options, 'resize.auto_orient')
            )
            &&  ! Arr::has($options, 'auto-orient')
        ) {
            $options = array_merge(['auto-orient' => []], $options);

            Arr::forget($options, [
                'resize.auto-orient',
                'resize.auto_orient',
            ]);
        }

        // Convert to array for fluent syntax support
        $converted = [];

        foreach ($options as $key => $value) {
            if ($value instanceof Arrayable) {
                foreach ($value->toArray() as $nestedKey => $nestedValue) {
                    $converted[$nestedKey] = $nestedValue;
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
    protected function shouldMerge($variantName)
    {
        return  ! Arr::has($this->variants, $variantName)
            &&  ! Arr::get($this->exclude, $variantName);
    }

    /**
     * Mark variant name to be excluded from any further merges.
     *
     * @param string $variantName
     */
    protected function markExcluded($variantName)
    {
        $this->exclude[ $variantName ] = true;
    }
}
