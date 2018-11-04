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
        if ( ! array_has($config, 'variants')) {
            $config['variants'] = config('paperclip.variants.default');
        }

        $extensions = [];
        $urls       = [];

        // Normalize variant definitions
        $variants = [];

        foreach (array_get($config, 'variants', []) as $variantName => $options) {

            if ($options instanceof Variant) {

                $variantName = $options->getName();

                if ($options->getExtension()) {
                    $extensions[ $variantName ] = $options->getExtension();
                }

                if ($options->getUrl()) {
                    $urls[ $variantName ] = $options->getUrl();
                }

                $options = $options->getSteps();
            }

            $variants[ $variantName ] = $this->normalizeVariantConfigEntry($options);

        }

        array_set($config, 'variants', $variants);
        unset($variants);


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

}
