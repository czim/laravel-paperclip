<?php
namespace Czim\Paperclip\Config;

class PaperclipConfig extends AbstractConfig
{

    /**
     * Takes the set config and creates a normalized version.
     *
     * This can also take stapler configs and normalize them for paperclip.
     *
     * @param array $config
     * @return array
     */
    protected function normalizeConfig(array $config)
    {
        $hasVariantsConfigured = array_has($config, 'variants');

        $variantList = $this->castVariantsToVariantList(array_get($config, 'variants', []));

        if ( ! $hasVariantsConfigured || $this->shouldMergeDefaultVariants()) {
            $variantList->mergeDefault(config('paperclip.variants.default', []));
        }

        $extensions = $variantList->extensions();
        $urls       = $variantList->urls();

        array_set($config, 'variants', $variantList->variants());


        // Merge in extensions set through indirect means.
        if (count($extensions)) {
            array_set(
                $config,
                'extensions',
                array_merge(array_get($config, 'extensions', []), $extensions)
            );
        }

        // Merge in default URLs set through indirect means.
        if (count($urls)) {
            array_set(
                $config,
                'urls',
                array_merge(array_get($config, 'urls', []), $urls)
            );
        }

        return $config;
    }

    /**
     * @param array $variants
     * @return VariantList
     */
    protected function castVariantsToVariantList(array $variants)
    {
        return new VariantList($variants);
    }

    /**
     * Returns whether default configured variants should always be merged in.
     *
     * @return bool
     */
    protected function shouldMergeDefaultVariants()
    {
        return (bool) config('paperclip.variants.merge-default');
    }

}
