<?php

declare(strict_types=1);

namespace Czim\Paperclip\Config;

use Illuminate\Support\Arr;

class PaperclipConfig extends AbstractConfig
{
    /**
     * {@inheritDoc}
     */
    protected function normalizeConfig(array $config): array
    {
        $hasVariantsConfigured = Arr::has($config, 'variants');

        $variantList = $this->castVariantsToVariantList(Arr::get($config, 'variants', []));

        if ( ! $hasVariantsConfigured || $this->shouldMergeDefaultVariants()) {
            $variantList->mergeDefault(config('paperclip.variants.default', []));
        }

        $extensions = $variantList->extensions();
        $urls       = $variantList->urls();

        Arr::set($config, 'variants', $variantList->variants());


        // Merge in extensions set through indirect means.
        if (count($extensions)) {
            Arr::set(
                $config,
                'extensions',
                array_merge(Arr::get($config, 'extensions', []), $extensions)
            );
        }

        // Merge in default URLs set through indirect means.
        if (count($urls)) {
            Arr::set(
                $config,
                'urls',
                array_merge(Arr::get($config, 'urls', []), $urls)
            );
        }

        return $config;
    }

    /**
     * @param array<string, mixed> $variants
     * @return VariantList
     */
    protected function castVariantsToVariantList(array $variants): VariantList
    {
        return new VariantList($variants);
    }

    /**
     * Returns whether default configured variants should always be merged in.
     *
     * @return bool
     */
    protected function shouldMergeDefaultVariants(): bool
    {
        return (bool) config('paperclip.variants.merge-default');
    }

}
