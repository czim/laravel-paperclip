<?php
namespace Czim\Paperclip\Test\Path;

use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Path\Interpolator;
use Czim\Paperclip\Test\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;

class InterpolatorTest extends TestCase
{

    ///**
    // * @test
    // */
    //function it_shit()
    //{
    //    $interpolator = new Interpolator;
    //
    //    $model = $this->getMockModel();
    //    $model->shouldReceive('getKey')->andReturn(13);
    //
    //    $attachment = $this->getMockAttachment();
    //    $attachment->shouldReceive('name')->once()->andReturn('attributename');
    //    $attachment->shouldReceive('getInstance')->andReturn($model);
    //    $attachment->shouldReceive('getInstanceClass')->once()->andReturn('App\\Models\\Test');
    //    $attachment->shouldReceive('originalFilename')->once()->andReturn('testing.txt');
    //
    //    $result = $interpolator->interpolate('/:class_name/:attachment/:style/:hash.:extension', $attachment, 'variant');
    //
    //    dd($result);
    //}

    /**
     * @test
     */
    function it_interpolates_the_default_path_placeholders()
    {
        $interpolator = new Interpolator;

        $model = $this->getMockModel();
        $model->shouldReceive('getKey')->andReturn(13);

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('name')->once()->andReturn('attributename');
        $attachment->shouldReceive('getInstance')->andReturn($model);
        $attachment->shouldReceive('getInstanceClass')->once()->andReturn('App\\Models\\Test');

        $result = $interpolator->interpolate(':class/:id_partition/:attribute', $attachment, 'variant');

        static::assertEquals('App/Models/Test/000/000/013/attributename', $result);
    }

    /**
     * @test
     */
    function it_interpolates_filename()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('originalFilename')->once()->andReturn('testing.gif');

        $result = $interpolator->interpolate('test/:filename', $attachment, 'variant');

        static::assertEquals('test/testing.gif', $result);
    }

    /**
     * @test
     */
    function it_interpolates_url()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('url')->once()->andReturn('http://testing/url');

        $result = $interpolator->interpolate(':url', $attachment, 'variant');

        static::assertEquals('http://testing/url', $result);
    }

    /**
     * @test
     */
    function it_interpolates_app_root()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();

        $result = $interpolator->interpolate(':app_root', $attachment, 'variant');

        static::assertEquals(app_path(), $result);
    }

    /**
     * @test
     */
    function it_interpolates_class()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getInstanceClass')->twice()->andReturn('App\\TestClass\\Name');

        $result = $interpolator->interpolate(':class/:class_name', $attachment, 'variant');

        static::assertEquals('App/TestClass/Name/Name', $result);
    }

    /**
     * @test
     */
    function it_interpolates_namespace()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getInstanceClass')->once()->andReturn('App\\TestClass\\Name');

        $result = $interpolator->interpolate(':namespace', $attachment, 'variant');

        static::assertEquals('App/TestClass', $result);
    }

    /**
     * @test
     */
    function it_interpolates_attribute_name()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('name')->once()->andReturn('attribute');

        $result = $interpolator->interpolate(':name/test', $attachment, 'variant');

        static::assertEquals('attribute/test', $result);
    }

    /**
     * @test
     */
    function it_interpolates_basename()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('originalFilename')->once()->andReturn('testing.txt');

        $result = $interpolator->interpolate(':basename/test', $attachment, 'variant');

        static::assertEquals('testing/test', $result);
    }

    /**
     * @test
     */
    function it_interpolates_extension()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('originalFilename')->once()->andReturn('testing.txt');

        $result = $interpolator->interpolate(':extension/test', $attachment, 'variant');

        static::assertEquals('txt/test', $result);
    }

    /**
     * @test
     */
    function it_interpolates_secure_hash()
    {
        $interpolator = new Interpolator;

        $model = $this->getMockModel();
        $model->shouldReceive('getKey')->andReturn(13);

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getInstance')->andReturn($model);
        $attachment->shouldReceive('size')->once()->andReturn(333);
        $attachment->shouldReceive('originalFilename')->once()->andReturn('testing.txt');

        $result = $interpolator->interpolate(':secure_hash', $attachment, 'variant');

        static::assertEquals('b7e89900b301888e5e9035e2117a36642e5ef4330389e0fde88db7009007908d', $result);
    }

    /**
     * @test
     */
    function it_interpolates_hash()
    {
        $interpolator = new Interpolator;

        $model = $this->getMockModel();
        $model->shouldReceive('getKey')->andReturn(13);

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getInstance')->andReturn($model);

        $result = $interpolator->interpolate(':hash', $attachment, 'variant');

        static::assertEquals('3fdba35f04dc8c462986c992bcf875546257113072a909c162f7e470e581e278', $result);
    }

    /**
     * @test
     */
    function it_interpolates_id_partion_for_string_id()
    {
        $interpolator = new Interpolator;

        $model = $this->getMockModel();
        $model->shouldReceive('getKey')->andReturn('astring');

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getInstance')->andReturn($model);

        $result = $interpolator->interpolate(':id_partition', $attachment, 'variant');

        static::assertEquals('ast/rin/g', $result);
    }

    /**
     * @test
     */
    function it_interpolates_id_partion_for_string_id_with_control_characters()
    {
        $interpolator = new Interpolator;

        $model = $this->getMockModel();
        $model->shouldReceive('getKey')->andReturn("astring\n\t");

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getInstance')->andReturn($model);

        $result = $interpolator->interpolate(':id_partition', $attachment, 'variant');

        static::assertEquals('ec3/1c8/43d', $result);
    }

    /**
     * @test
     */
    function it_interpolates_attachment()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('name')->andReturn('image');

        $result = $interpolator->interpolate(':attachment', $attachment, 'variant');

        static::assertEquals('images', $result);
    }

    /**
     * @test
     */
    function it_interpolates_style_for_variant()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();

        $result = $interpolator->interpolate(':style', $attachment, 'variant');

        static::assertEquals('variant', $result);
    }

    /**
     * @test
     */
    function it_interpolates_style_for_original()
    {
        $interpolator = new Interpolator;

        $attachment = $this->getMockAttachment();
        $attachment->shouldReceive('getConfig')->once()->andReturn(['default-variant' => 'original']);

        $result = $interpolator->interpolate(':style', $attachment);

        static::assertEquals('original', $result);
    }


    /**
     * @return \Mockery\MockInterface|AttachmentInterface
     */
    protected function getMockAttachment()
    {
        return Mockery::mock(AttachmentInterface::class);
    }

    /**
     * @return \Mockery\MockInterface|Model
     */
    protected function getMockModel()
    {
        return Mockery::mock(Model::class);
    }

}
