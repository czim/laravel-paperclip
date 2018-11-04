<?php
namespace Czim\Paperclip\Test\Config;

use Czim\Paperclip\Config\PaperclipConfig;
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

}
