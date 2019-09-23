<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Attachment;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\FileHandling\Contracts\Storage\TargetInterface;
use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Config\PaperclipConfig;
use Czim\Paperclip\Contracts\FileHandlerFactoryInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Czim\Paperclip\Test\TestCase;
use Hamcrest\Matchers;
use Mockery;

class AttachmentTest extends TestCase
{

    /**
     * @test
     */
    function it_takes_and_returns_an_instance()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setInstance($model));

        static::assertSame($model, $attachment->getInstance());
        static::assertEquals(get_class($model), $attachment->getInstanceClass());
    }

    /**
     * @test
     */
    function it_takes_and_returns_a_name()
    {
        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setName('test'));

        static::assertEquals('test', $attachment->name());
    }

    /**
     * @test
     */
    function it_takes_an_interpolator()
    {
        /** @var InterpolatorInterface $interpolator */
        $interpolator = Mockery::mock(InterpolatorInterface::class);

        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setInterpolator($interpolator));
    }

    /**
     * @test
     */
    function it_takes_and_returns_a_configuration()
    {
        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setConfig(
            new PaperclipConfig(['test' => true]))
        );

        static::assertEquals(['test' => true], $attachment->getConfig());
    }

    /**
     * @test
     */
    function it_takes_and_returns_a_storage_identifier_and_handler()
    {
        $handler = $this->getMockHandler();
        $this->app->instance(FileHandlerFactoryInterface::class, $this->getMockHandlerFactory($handler));

        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setStorage('test'));

        static::assertSame('test', $attachment->getStorage());
        static::assertSame($handler, $attachment->getHandler());
    }

    /**
     * @test
     */
    function it_returns_variant_keys_as_configured()
    {
        $attachment = new Attachment;
        $attachment->setConfig(new PaperclipConfig([
            'variants' => [
                'some'    => [],
                'variant' => [],
                'keys'    => [],
            ],
        ]));

        static::assertEquals(['some', 'variant', 'keys'], $attachment->variants());
    }

    /**
     * @test
     */
    function it_returns_the_url_for_a_variant()
    {
        $model        = $this->getTestModel();
        $handler      = $this->getMockHandler();
        $interpolator = $this->getMockInterpolator();

        $this->app->instance(FileHandlerFactoryInterface::class, $this->getMockHandlerFactory($handler));

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setStorage('paperclip');
        $attachment->setInterpolator($interpolator);
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');

        $handler->shouldReceive('variantUrlsForTarget')->once()
            ->with(Matchers::any(TargetInterface::class), ['variantkey'])
            ->andReturn(['variantkey' => 'http://fake.url/file/variantkey']);

        static::assertEquals('http://fake.url/file/variantkey', $attachment->url('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_original_path()
    {
        $interpolator = $this->getMockInterpolator();

        $attachment = new Attachment;
        $attachment->setInterpolator($interpolator);

        $interpolator->shouldReceive('interpolate')
            ->once()
            ->with(
                ':class/:id_partition/:attribute/:variant/:filename',
                $attachment
            )
            ->andReturn('file/test.png');

        static::assertEquals('file/test.png', $attachment->path());
    }

    /**
     * @test
     */
    function it_returns_the_variant_path_for_a_variant()
    {
        $model        = $this->getTestModel();
        $handler      = $this->getMockHandler();
        $interpolator = $this->getMockInterpolator();

        $this->app->instance(FileHandlerFactoryInterface::class, $this->getMockHandlerFactory($handler));

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setStorage('paperclip');
        $attachment->setInterpolator($interpolator);
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');

        $interpolator->shouldReceive('interpolate')->once()
            ->with(':class/:id_partition/:attribute/:variant/:filename', $attachment, 'variantkey')
            ->andReturn('file/variantkey/test.png');

        static::assertEquals('file/variantkey/test.png', $attachment->variantPath('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_filename_for_a_variant()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');

        static::assertEquals('test.png', $attachment->variantFilename('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_filename_for_a_variant_if_it_has_a_different_extension()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');
        $attachment->setConfig(new PaperclipConfig([
            'extensions' => [
                'variantkey' => 'txt',
            ],
        ]));

        $model->setAttribute('image_file_name', 'test.png');

        static::assertEquals('test.txt', $attachment->variantFilename('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_extension_for_a_variant_if_it_is_configured_with_a_different_extension()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setConfig(new PaperclipConfig([
            'extensions' => [
                'variantkey' => 'txt',
            ],
        ]));
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');

        static::assertEquals('txt', $attachment->variantExtension('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_extension_for_a_variant_if_it_is_stored_in_variants_json_data_with_a_different_extension()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setConfig(new PaperclipConfig([
            'attributes' => [
                'variants' => true,
            ],
        ]));
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');
        $model->setAttribute('image_variants', json_encode(['variantkey' => ['ext' => 'txt']]));

        static::assertEquals('txt', $attachment->variantExtension('variantkey'));
    }
    
    /**
     * @test
     */
    function it_returns_the_content_type_for_a_variant_if_it_is_the_same_as_the_original()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');
        $model->setAttribute('image_content_type', 'image/png');

        static::assertEquals('image/png', $attachment->variantContentType('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_content_type_for_a_variant_if_it_is_configured_with_a_different_type()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setConfig(new PaperclipConfig([
            'types' => [
                'variantkey' => 'text/plain',
            ],
        ]));
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');
        $model->setAttribute('image_content_type', 'image/png');

        static::assertEquals('text/plain', $attachment->variantContentType('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_content_type_for_a_variant_if_it_is_stored_with_a_different_type()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setConfig(new PaperclipConfig([
            'attributes' => [
                'variants' => true,
            ],
        ]));
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');
        $model->setAttribute('image_content_type', 'image/png');
        $model->setAttribute('image_variants', json_encode(['variantkey' => ['type' => 'text/plain']]));

        static::assertEquals('text/plain', $attachment->variantContentType('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_whether_the_attachment_is_filled()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setName('attachment');
        $attachment->setInstance($model);

        static::assertFalse($attachment->exists());

        $model->attachment = 'testing';

        static::assertTrue($attachment->exists());
    }


    // ------------------------------------------------------------------------------
    //      Properties
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_the_created_at_attribute()
    {
        $model = $this->getTestModel();
        $model->image_created_at = '2017-01-01 00:00:00';

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');
        $attachment->setConfig(new PaperclipConfig([
            'attributes' => [
                'created_at' => true,
            ],
        ]));

        static::assertEquals('2017-01-01 00:00:00', $attachment->createdAt());
    }

    /**
     * @test
     */
    function it_returns_the_updated_at_attribute()
    {
        $model = $this->getTestModel();
        $model->image_updated_at = '2017-01-01 00:00:00';

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');

        static::assertEquals('2017-01-01 00:00:00', $attachment->updatedAt());
    }

    /**
     * @test
     */
    function it_returns_the_content_type_attribute()
    {
        $model = $this->getTestModel();
        $model->image_content_type = 'video/mpeg';

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');

        static::assertEquals('video/mpeg', $attachment->contentType());
    }

    /**
     * @test
     */
    function it_returns_the_size_attribute()
    {
        $model = $this->getTestModel();
        $model->image_file_size = 333;

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');

        static::assertEquals(333, $attachment->size());
    }

    /**
     * @test
     */
    function it_returns_the_original_file_name_attribute()
    {
        $model = $this->getTestModel();
        $model->image_file_name = 'test.png';

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setName('image');

        static::assertEquals('test.png', $attachment->originalFilename());
    }

    /**
     * @test
     */
    function it_returns_a_configured_fallback_url_when_no_attachment_is_stored()
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'url'  => 'http://fallback-test-url',
            'urls' => [
                'variant' => 'http://variant-fallback-test-url',
            ],
        ]);

        static::assertEquals('http://fallback-test-url', $model->attachment->url());
    }

    /**
     * @test
     */
    function it_returns_a_configured_fallback_url_for_a_variant_when_no_attachment_is_stored()
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'url'  => 'http://fallback-test-url',
            'urls' => [
                'variant' => 'http://variant-fallback-test-url',
            ],
        ]);

        static::assertEquals('http://variant-fallback-test-url', $model->attachment->url('variant'));
    }

    
    /**
     * @return Mockery\MockInterface|Mockery\Mock|FileHandlerInterface
     */
    protected function getMockHandler()
    {
        return Mockery::mock(FileHandlerInterface::class);
    }

    /**
     * @param FileHandlerInterface $handler
     * @return Mockery\MockInterface|Mockery\Mock|FileHandlerFactoryInterface
     */
    protected function getMockHandlerFactory(FileHandlerInterface $handler)
    {
        $mock = Mockery::mock(FileHandlerFactoryInterface::class);

        $mock->shouldReceive('create')->andReturn($handler);

        return $mock;
    }

    /**
     * @return Mockery\MockInterface|Mockery\Mock|InterpolatorInterface
     */
    protected function getMockInterpolator()
    {
        return Mockery::mock(InterpolatorInterface::class);
    }

}
