<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Config;

use Czim\Paperclip\Config\PaperclipConfig;
use Czim\Paperclip\Config\Steps\AutoOrientStep;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Test\TestCase;

class PaperclipConfigTest extends TestCase
{

    /**
     * @test
     */
    function it_can_be_instantiated_with_empty_data()
    {
        $config = new PaperclipConfig([]);

        static::assertEquals([], $config->getOriginalConfig());
        static::assertEquals(['variants' => []], $config->toArray());
    }

    /**
     * @test
     */
    function it_takes_array_data_and_returns_configuration_values()
    {
        $config = new PaperclipConfig([
            'attributes'     => [
                'size'         => false,
                'content_type' => false,
                'updated_at'   => false,
                'created_at'   => false,
                'variants'     => false,
            ],
            'variants'       => [
                'one' => [
                    'resize' => [
                        'dimensions' => '50x50',
                    ],
                ],
            ],
            'extensions'     => [
                'one' => 'png',
            ],
            'types'          => [
                'one' => 'image/png',
            ],
            'keep-old-files' => true,
            'preserve-files' => true,
            'storage'        => 'some-disk',
            'path'           => '/relative/path',
            'variant-path'   => '/relative/path/:variant',
            'url'            => 'default-url',
            'urls'           => [
                'one' => 'default-url-variant',
            ],
            'before'         => 'test@test',
            'after'          => 'test@another',
        ]);


        static::assertEquals('image/png', $config->variantMimeType('one'));
        static::assertEquals('some-disk', $config->storageDisk());
        static::assertEquals('/relative/path', $config->path());
        static::assertEquals('/relative/path/:variant', $config->variantPath());
        static::assertEquals('default-url', $config->defaultUrl());
        static::assertEquals('default-url-variant', $config->defaultVariantUrl('one'));

        static::assertEquals(['resize' => ['dimensions' => '50x50']], $config->variantConfig('one'));
        static::assertTrue($config->hasVariantConfig('one'));
        static::assertFalse($config->hasVariantConfig('does-not-exist'));

        static::assertEquals('png', $config->variantExtension('one'));
        static::assertEquals(['one' => 'png'], $config->variantExtensions());

        static::assertEquals('test@test', $config->beforeCallable());
        static::assertEquals('test@another', $config->afterCallable());

        static::assertFalse($config->sizeAttribute());
        static::assertFalse($config->contentTypeAttribute());
        static::assertFalse($config->createdAtAttribute());
        static::assertFalse($config->updatedAtAttribute());
        static::assertFalse($config->variantsAttribute());
        static::assertFalse($config->attributeProperty('created_at'));

        static::assertTrue($config->keepOldFiles());
        static::assertTrue($config->preserveFiles());
    }

    /**
     * @test
     */
    function it_keeps_the_auto_orient_and_resize_steps_in_the_right_order()
    {
        $config = new PaperclipConfig([
            'variants' => [
                Variant::make('testing')->steps([
                    AutoOrientStep::make(),
                    ResizeStep::make()->width(480)->height(270)->crop(),
                ]),
            ],
        ]);

        $stepKeys = array_keys($config->variantConfig('testing'));
        static::assertEquals(['auto-orient', 'resize'], $stepKeys);
    }

    /**
     * @test
     */
    function it_uses_default_global_variants_if_no_variants_are_configured()
    {
        $this->app['config']->set('paperclip.variants.default', [
            'one' => [
                'resize' => [
                    'dimensions' => '50x50',
                ],
            ],
        ]);

        $config = new PaperclipConfig([
            'types' => [
                'one' => 'image/png',
            ],
        ]);

        static::assertTrue($config->hasVariantConfig('one'));

        static::assertEquals('image/png', $config->variantMimeType('one'));
    }

    /**
     * @test
     */
    function it_merges_in_default_global_variants_that_are_not_configured_if_merge_default_enabled()
    {
        $this->app['config']->set('paperclip.variants.merge-default', true);
        $this->app['config']->set('paperclip.variants.default', [
            'four'  => [
                'resize' => [
                    'dimensions' => '50x50',
                ],
            ],
            'two'   => [
                'resize' => [
                    'dimensions' => '50x50',
                ],
            ],
            'three' => [
                'resize' => [
                    'dimensions' => '50x50',
                ],
            ],
        ]);

        $config = new PaperclipConfig([
            'variants' => [
                'one'   => [
                    'resize' => [
                        'dimensions' => '50x50',
                    ],
                ],
                // test whether variant set in attachment config does not get overrule by global
                'three' => [
                    'resize' => [
                        'dimensions' => '100x100',
                    ],
                ],
                // test whether variant set by object config gets handled correctly
                (new Variant('four'))->steps([
                    (new ResizeStep())->square(100),
                ])
            ],
        ]);

        static::assertTrue($config->hasVariantConfig('one'));
        static::assertTrue($config->hasVariantConfig('two'));
        static::assertTrue($config->hasVariantConfig('three'));
        static::assertTrue($config->hasVariantConfig('four'));

        static::assertEquals(
            '100x100',
            $config->variantConfig('three')['resize']['dimensions'],
            "Variant 'three' should not be overridden by global configuration"
        );
        static::assertEquals(
            '100x100',
            $config->variantConfig('four')['resize']['dimensions'],
            "Variant 'four' should not be overridden by global configuration"
        );
    }

    /**
     * @test
     */
    function it_does_not_merge_in_default_global_variants_that_included_as_a_literal_false()
    {
        $this->app['config']->set('paperclip.variants.merge-default', true);
        $this->app['config']->set('paperclip.variants.default', [
            'two'   => [
                'resize' => [
                    'dimensions' => '50x50',
                ],
            ],
        ]);

        $config = new PaperclipConfig([
            'variants' => [
                'one'   => [
                    'resize' => [
                        'dimensions' => '50x50',
                    ],
                ],
                // test whether a variant set globally is left out when set to literal false on attachment
                'two' => false,
                // test whether any old variant defined by literal false is ignored
                'three' => false,
            ],
        ]);

        static::assertTrue($config->hasVariantConfig('one'));
        static::assertFalse($config->hasVariantConfig('two'));
        static::assertFalse($config->hasVariantConfig('three'));
    }

}
