<?php
namespace Czim\Paperclip\Test\Attachment;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\FileHandling\Contracts\Storage\PathHelperInterface;
use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Czim\Paperclip\Test\TestCase;
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
    function it_takes_a_path_helper()
    {
        /** @var PathHelperInterface $helper */
        $helper = Mockery::mock(PathHelperInterface::class);

        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setPathHelper($helper));
    }

    /**
     * @test
     */
    function it_takes_and_returns_a_configuration()
    {
        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setConfig(['test' => true]));

        static::assertEquals(['test' => true], $attachment->getConfig());
    }

    /**
     * @test
     */
    function it_takes_and_returns_a_file_handler()
    {
        $handler = $this->getMockHandler();

        $attachment = new Attachment;
        static::assertSame($attachment, $attachment->setHandler($handler));

        static::assertSame($handler, $attachment->getHandler());
    }

    /**
     * @test
     */
    function it_returns_variant_keys_as_configured()
    {
        $attachment = new Attachment;
        $attachment->setConfig([
            'variants' => [
                'some'    => [],
                'variant' => [],
                'keys'    => [],
            ],
        ]);

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

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setHandler($handler);
        $attachment->setInterpolator($interpolator);
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');

        $interpolator->shouldReceive('interpolate')->once()
            ->with(':class/:id_partition/:attribute', $attachment)
            ->andReturn('file/');

        $handler->shouldReceive('variantUrlsForBasePath')->once()
            ->with('file/', 'test.png', ['variantkey'])
            ->andReturn(['variantkey' => 'http://fake.url/file/variantkey']);

        static::assertEquals('http://fake.url/file/variantkey', $attachment->url('variantkey'));
    }

    /**
     * @test
     */
    function it_returns_the_variant_path_for_a_variant()
    {
        $model        = $this->getTestModel();
        $handler      = $this->getMockHandler();
        $interpolator = $this->getMockInterpolator();
        $helper       = $this->getMockPathHelper();

        $attachment = new Attachment;
        $attachment->setInstance($model);
        $attachment->setHandler($handler);
        $attachment->setPathHelper($helper);
        $attachment->setInterpolator($interpolator);
        $attachment->setName('image');

        $model->setAttribute('image_file_name', 'test.png');

        $interpolator->shouldReceive('interpolate')->once()
            ->with(':class/:id_partition/:attribute', $attachment)
            ->andReturn('file/');

        $helper->shouldReceive('addVariantToBasePath')->once()
            ->with('file/', 'variantkey')
            ->andReturn('file/variantkey');

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
        $attachment->setConfig([
            'extensions' => [
                'variantkey' => 'txt',
            ],
        ]);

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
        $attachment->setConfig([
            'extensions' => [
                'variantkey' => 'txt',
            ],
        ]);
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
        $attachment->setConfig([
            'attributes' => [
                'variants' => true,
            ],
        ]);
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
        $attachment->setConfig([
            'types' => [
                'variantkey' => 'text/plain',
            ],
        ]);
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
        $attachment->setConfig([
            'attributes' => [
                'variants' => true,
            ],
        ]);
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
        $attachment->setConfig([
            'attributes' => [
                'created_at' => true,
            ],
        ]);

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

    
    // ------------------------------------------------------------------------------
    //      Stapler Compatibility
    // ------------------------------------------------------------------------------
    
    /**
     * @test
     */
    function it_uses_stapler_styles_key_for_variants()
    {
        $attachment = new Attachment;
        $attachment->setConfig([
            'styles' => [
                'some'    => '100x100',
                'variant' => '50x30',
                'keys'    => '40x',
            ],
        ]);

        static::assertEquals(['some', 'variant', 'keys'], $attachment->variants());
    }
    
    /**
     * @test
     */
    function it_converts_stapler_dimensions_to_resize_steps()
    {
        // todo
        // should test in integration
    }
    
    /**
     * @test
     */
    function it_extracts_a_stapler_auto_orient_flag_to_its_own_variant_step()
    {
        // todo
        // should test in integration
    }


    /**
     * @return Mockery\MockInterface|Mockery\Mock|FileHandlerInterface
     */
    protected function getMockHandler()
    {
        return Mockery::mock(FileHandlerInterface::class);
    }

    /**
     * @return Mockery\MockInterface|Mockery\Mock|InterpolatorInterface
     */
    protected function getMockInterpolator()
    {
        return Mockery::mock(InterpolatorInterface::class);
    }

    /**
     * @return Mockery\MockInterface|Mockery\Mock|PathHelperInterface
     */
    protected function getMockPathHelper()
    {
        return Mockery::mock(PathHelperInterface::class);
    }

}
