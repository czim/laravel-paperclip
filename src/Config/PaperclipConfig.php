<?php
namespace Czim\Paperclip\Config;

use Czim\FileHandling\Handler\FileHandler;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Illuminate\Contracts\Support\Arrayable;

class PaperclipConfig implements ConfigInterface
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $normalizedConfig;


    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config           = $config;
        $this->normalizedConfig = $this->normalizeConfig($config);
    }


    /**
     * @return bool
     */
    public function keepOldFiles()
    {
        return (bool) $this->getConfigValue('keep-old-files', false);
    }

    /**
     * @return bool
     */
    public function preserveFiles()
    {
        return (bool) $this->getConfigValue('preserve-files', false);
    }

    /**
     * @return string
     */
    public function storageDisk()
    {
        return $this->getConfigValue('storage');
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->getConfigValue('path');
    }

    /**
     * @return string
     */
    public function variantPath()
    {
        return $this->getConfigValue('variant-path');
    }

    /**
     * @return string
     */
    public function sizeAttribute()
    {
        return $this->getConfigValue('attributes.size');
    }

    /**
     * @return string
     */
    public function contentTypeAttribute()
    {
        return $this->getConfigValue('attributes.content_type');
    }

    /**
     * @return string
     */
    public function updatedAtAttribute()
    {
        return $this->getConfigValue('attributes.updated_at');
    }

    /**
     * @return string
     */
    public function createdAtAttribute()
    {
        return $this->getConfigValue('attributes.created_at');
    }

    /**
     * @return string
     */
    public function variantsAttribute()
    {
        return $this->getConfigValue('attributes.variants');
    }

    /**
     * Returns whether a configuration for a specific variant has been set.
     *
     * @param string $variant
     * @return bool
     */
    public function hasVariantConfig($variant)
    {
        return array_has($this->variantConfigs(), $variant);
    }

    /**
     * Returns the configuration array for a specific variant.
     *
     * @param string $variant
     * @return array
     */
    public function variantConfig($variant)
    {
        return array_get($this->variantConfigs(), $variant, []);
    }

    /**
     * Returns an array with the variant configurations set.
     *
     * @return array    associative, keyed by variant name
     */
    public function variantConfigs()
    {
       return array_get($this->normalizedConfig, FileHandler::CONFIG_VARIANTS);
    }

    /**
     * Returns the mimetype specifically configured for a given variant.
     *
     * @param string $variant
     * @return false|string
     */
    public function variantMimeType($variant)
    {
        return $this->getConfigValue("types.{$variant}") ?: false;
    }

    /**
     * Returns an array with extensions configured per variant.
     *
     * @return string[]     associative, keyed by variant name
     */
    public function variantExtensions()
    {
        return $this->getConfigValue('extensions', []);
    }

    /**
     * Returns the extension specifically configured for a given variant.
     *
     * Note that this does not determine the extension for the variant,
     * just what extension Paperclip should expect the created file to have.
     *
     * @param string $variant
     * @return false|string
     */
    public function variantExtension($variant)
    {
        return $this->getConfigValue("extensions.{$variant}") ?: false;
    }

    /**
     * Returns whether a given attribute property should be saved.
     *
     * Expected values: 'created_at', 'content_type', ...
     *
     * @param string $attribute
     * @return bool
     */
    public function attributeProperty($attribute)
    {
        return $this->getConfigValue("attributes.{$attribute}", true);
    }

    /**
     * Returns the hook callable to run before storing an attachment.
     *
     * @return callable|null
     */
    public function beforeCallable()
    {
        return $this->getConfigValue('before');
    }

    /**
     * Returns the hook callable to run after storing an attachment.
     *
     * @return callable|null
     */
    public function afterCallable()
    {
        return $this->getConfigValue('after');
    }


    /**
     * @return array
     */
    public function getOriginalConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->normalizedConfig;
    }


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
        if ( ! array_has($config, 'variants') && array_has($config, 'styles')) {
            $config['variants'] = array_get($config, 'styles', []);
        }
        array_forget($config, 'styles');

        if ( ! array_has($config, 'variants')) {
            $config['variants'] = config('paperclip.variants.default');
        }

        $extensions = [];

        // Normalize variant definitions
        $variants = [];

        foreach (array_get($config, 'variants', []) as $variantName => $options) {

            if ($options instanceof Variant) {

                $variantName = $options->getName();

                if ($options->getExtension()) {
                    $extensions[ $variantName ] = $options->getExtension();
                }

                $options = $options->getSteps();
            }

            $variants[ $variantName ] = $this->normalizeVariantConfigEntry($options);

        }

        array_set($config, 'variants', $variants);
        unset($variants);

        // Simple renames of stapler config keys
        $renames = [
            'url'            => 'path',
            'keep_old_files' => 'keep-old-files',
            'preserve_files' => 'preserve-files',
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

        // Merge in extensions set through indirect means.
        if (count($extensions)) {
            array_set(
                $config,
                'extensions',
                array_merge(array_get($config, 'extensions', []), $extensions)
            );
        }

        return $config;
    }

    /**
     * @param mixed $options
     * @return array
     */
    protected function normalizeVariantConfigEntry($options)
    {
        // Assume dimensions if a string (with dimensions)
        if (is_string($options)) {
            $options = ['resize' => ['dimensions' => $options]];
        }

        // Convert objects to arrays for fluent syntax support
        if ($options instanceof Arrayable) {
            $options = [ $options ];
        }

        if (array_key_exists('dimensions', $options)) {
            $options = ['resize' => $options];
        }

        // If auto-orient is set, extract it to its own step
        if (    (   array_get($options, 'resize.auto-orient')
                ||  array_get($options, 'resize.auto_orient')
            )
            &&  ! array_has($options, 'auto-orient')
        ) {
            $options = array_merge(['auto-orient' => []], $options);

            array_forget($options, [
                'resize.auto-orient',
                'resize.auto_orient',
            ]);
        }

        // Convert to array for fluent syntax support
        $converted = [];

        foreach ($options as $key => $value) {

            if ($value instanceof Arrayable) {
                $converted = array_merge($value->toArray(), $converted);
                continue;
            }

            $converted[ $key ] = $value;
        }

        return $converted;
    }

    /**
     * @param string      $key
     * @param string|null $default
     * @return mixed
     */
    protected function getConfigValue($key, $default = null)
    {
        if (array_has($this->normalizedConfig, $key)) {
            return array_get($this->normalizedConfig, $key);
        }

        // Fall back to default configured values
        $map = [
            'keep-old-files' => 'keep-old-files',
            'preserve-files' => 'preserve-files',
            'storage'        => 'storage.disk',
            'path'           => 'path.original',
            'variant-path'   => 'path.variant',

            'attributes.size'         => 'model.attributes.size',
            'attributes.content_type' => 'model.attributes.content_type',
            'attributes.updated_at'   => 'model.attributes.updated_at',
            'attributes.created_at'   => 'model.attributes.created_at',
            'attributes.variants'     => 'model.attributes.variants',
        ];

        if ( ! in_array($key, array_keys($map))) {
            return $default;
        }

        return config('paperclip.' . $map[ $key ], $default);
    }

}
