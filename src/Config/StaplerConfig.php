<?php
namespace Czim\Paperclip\Config;

class StaplerConfig extends PaperclipConfig
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
        // In Stapler, variants were called 'styles'.
        if ( ! array_has($config, 'variants') && array_has($config, 'styles')) {
            $config['variants'] = array_get($config, 'styles', []);
        }
        array_forget($config, 'styles');


        // Simple renames of stapler config keys.
        $renames = [
            'url'            => 'path',
            'keep_old_files' => 'keep-old-files',
            'preserve_files' => 'preserve-files',

            // Note that 'url' conflicts with with Paperclip.
            // In Stapler, 'url' is what is 'path' in Paperclip.
            // To allow for full configuration 'missing_url' is mapped to 'url',
            // even though this option was not present in Stapler.
            'missing_url' => 'url',
        ];

        foreach ($renames as $old => $new) {
            if ( ! array_has($config, $old)) {
                continue;
            }

            if ( ! array_has($config, $new)) {
                $config[ $new ] = array_get($config, $old);
            }
            array_forget($config, $old);
        }

        return parent::normalizeConfig($config);
    }

}
