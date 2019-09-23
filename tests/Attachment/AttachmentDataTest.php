<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\Paperclip\Test\Attachment;

use Czim\Paperclip\Attachment\AttachmentData;
use Czim\Paperclip\Test\TestCase;

class AttachmentDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_the_name()
    {
        $data = $this->getAttachmentData();

        static::assertEquals('attachment', $data->name());
    }

    /**
     * @test
     */
    function it_returns_the_configuration()
    {
        $data = $this->getAttachmentData();

        static::assertEquals(['url' => '/test/:filename'], $data->getConfig());
    }

    /**
     * @test
     */
    function it_returns_basic_attributes()
    {
        $data = $this->getAttachmentData();

        static::assertEquals('some.jpg', $data->originalFilename());
        static::assertEquals(511, $data->size());
        static::assertEquals('image/jpg', $data->contentType());
        static::assertEquals('2018-01-01 01:01:02', $data->updatedAt());
        static::assertEquals('2018-01-01 01:01:01', $data->createdAt());
        static::assertEquals(['a' => ['ext' => 'txt']], $data->variantsAttribute());
    }

    /**
     * @test
     */
    function it_returns_empty_values_if_basic_attributes_are_not_set()
    {
        $data = $this->getEmptyAttachmentData();

        static::assertNull($data->originalFilename());
        static::assertNull($data->size());
        static::assertNull($data->contentType());
        static::assertNull($data->updatedAt());
        static::assertNull($data->createdAt());
        static::assertEquals([], $data->variantsAttribute());
    }

    /**
     * @test
     */
    function it_returns_variant_attributes()
    {
        $data = $this->getAttachmentData();

        static::assertEquals('alternative', $data->variantFilename('a'));
        static::assertEquals('txt', $data->variantExtension('a'));
        static::assertEquals('text/plain', $data->variantContentType('a'));
    }

    /**
     * @test
     */
    function it_returns_false_values_if_variant_attributes_are_unset()
    {
        $data = $this->getAttachmentData();

        static::assertFalse($data->variantFilename('b'));
        static::assertFalse($data->variantExtension('b'));
        static::assertFalse($data->variantContentType('b'));
    }

    /**
     * @test
     */
    function it_returns_instance_attributes()
    {
        $data = $this->getAttachmentData();

        static::assertEquals(13, $data->getInstanceKey());
        static::assertEquals('Some\\TestNamespace', $data->getInstanceClass());
    }

    /**
     * @return AttachmentData
     */
    protected function getAttachmentData()
    {
        return new AttachmentData(
            'attachment',
            ['url' => '/test/:filename'],
            [
                'file_name'    => 'some.jpg',
                'file_size'    => 511,
                'content_type' => 'image/jpg',
                'updated_at'   => '2018-01-01 01:01:02',
                'created_at'   => '2018-01-01 01:01:01',
                'variants'     => [
                    'a' => [
                        'ext' => 'txt',
                    ],
                ],
            ],
            [
                'a' => [
                    'file_name'    => 'alternative',
                    'content_type' => 'text/plain',
                    'extension'    => 'txt',
                ],
            ],
            13,
            'Some\\TestNamespace'
        );
    }

    /**
     * @return AttachmentData
     */
    protected function getEmptyAttachmentData()
    {
        return new AttachmentData(
            'attachment',
            [],
            [],
            [],
            null,
            'Some\\TestNamespace'
        );
    }
}
