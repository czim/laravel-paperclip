<?php

declare(strict_types=1);

namespace Czim\Paperclip\Test\Attachment;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Path\Interpolator;
use Czim\Paperclip\Test\TestCase;

class AttachmentSerializationTest extends TestCase
{
    /**
     * @test
     */
    public function it_serializes_and_unserializes_model_with_an_attachment(): void
    {
        $model = $this->getTestModel();

        $interpolator = new Interpolator();

        $attachment = new Attachment;
        $attachment->setName('testing');
        $attachment->setInstance($model);
        $attachment->setInterpolator($interpolator);
        $attachment->setStorage('paperclip');

        $serialized = serialize($attachment);

        // Check if the file handler is actually restored properly after serialization.
        static::assertInstanceOf(FileHandlerInterface::class, $attachment->getHandler());
        static::assertIsString($serialized);

        /** @var Attachment $unserialized */
        $unserialized = unserialize($serialized);

        static::assertInstanceOf(Attachment::class, $unserialized);
        static::assertInstanceOf(FileHandlerInterface::class, $unserialized->getHandler());
    }
}
