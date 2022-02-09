<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Path;

use Czim\Paperclip\Contracts\AttachmentDataInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Czim\Paperclip\Path\InterpolatingTarget;
use Czim\Paperclip\Test\TestCase;
use Hamcrest\Matchers;
use Mockery;

class InterpolatingTargetTest extends TestCase
{

    /**
     * @test
     */
    function it_uses_a_different_path_for_variants_when_given()
    {
        $interpolator = $this->getMockInterpolator();

        $interpolator->shouldReceive('interpolate')
            ->once()
            ->with('base/original/:filename', Matchers::any(AttachmentDataInterface::class))
            ->andReturn('base/original/testing.txt');

        $interpolator->shouldReceive('interpolate')
            ->once()
            ->with('base/variants/:variant/:filename', Matchers::any(AttachmentDataInterface::class), 'variant')
            ->andReturn('base/variants/variant/testing.txt');

        $target = new InterpolatingTarget(
            $interpolator,
            $this->getMockAttachmentData(),
            'base/original/:filename',
            'base/variants/:variant/:filename'
        );

        static::assertEquals('base/original/testing.txt', $target->original());
        static::assertEquals('base/variants/variant/testing.txt', $target->variant('variant'));
    }

    /**
     * @return Mockery\MockInterface|Mockery\Mock|InterpolatorInterface
     */
    protected function getMockInterpolator()
    {
        return Mockery::mock(InterpolatorInterface::class);
    }

    /**
     * @return Mockery\MockInterface|Mockery\Mock|AttachmentDataInterface
     */
    protected function getMockAttachmentData()
    {
        return Mockery::mock(AttachmentDataInterface::class);
    }

}
