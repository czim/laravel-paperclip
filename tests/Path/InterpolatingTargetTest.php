<?php

declare(strict_types=1);

namespace Czim\Paperclip\Test\Path;

use Czim\Paperclip\Contracts\AttachmentDataInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Czim\Paperclip\Path\InterpolatingTarget;
use Czim\Paperclip\Test\TestCase;
use Hamcrest\Matchers;
use Mockery;
use Mockery\MockInterface;

class InterpolatingTargetTest extends TestCase
{
    /**
     * @test
     */
    public function it_uses_a_different_path_for_variants_when_given(): void
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
     * @return InterpolatorInterface&MockInterface
     */
    protected function getMockInterpolator(): MockInterface
    {
        return Mockery::mock(InterpolatorInterface::class);
    }

    /**
     * @return AttachmentDataInterface&MockInterface
     */
    protected function getMockAttachmentData(): MockInterface
    {
        return Mockery::mock(AttachmentDataInterface::class);
    }
}
