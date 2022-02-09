<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Attachment;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Test\TestCase;

class AttachmentSerializationTest extends TestCase
{

    /**
     * @test
     */
    function it_serializes_and_unserializes_model_with_an_attachment()
    {
        $model = $this->getTestModel();

        $attachment = new Attachment;
        $attachment->setInstance($model);

        $attachment->setStorage('paperclip');

        $serialized = serialize($attachment);

        // Check if the file handler is actually restored properly after serialization
        static::assertInstanceOf(FileHandlerInterface::class, $attachment->getHandler());
        static::assertIsString($serialized);

        /** @var Attachment $unserialized */
        $unserialized = unserialize($serialized);

        static::assertInstanceOf(Attachment::class, $unserialized);
        static::assertInstanceOf(FileHandlerInterface::class, $unserialized->getHandler());
    }
}
