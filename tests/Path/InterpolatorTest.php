<?php
namespace Czim\Paperclip\Test\Path;

use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Path\Interpolator;
use Czim\Paperclip\Test\TestCase;
use Illuminate\Database\Eloquent\Model;
use Mockery;

class InterpolatorTest extends TestCase
{

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
