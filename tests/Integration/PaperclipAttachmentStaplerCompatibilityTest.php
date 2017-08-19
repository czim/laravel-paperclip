<?php
namespace Czim\Paperclip\Test\Integration;

use Czim\Paperclip\Test\ProvisionedTestCase;
use SplFileInfo;

class PaperclipAttachmentStaplerCompatibilityTest extends ProvisionedTestCase
{

    /**
     * @test
     */
    function it_accepts_stapler_styles_and_resizes_configuration()
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'styles' => [
                'a' => '50x50',
                'b' => [
                    'dimensions'  => '40x40',
                    'auto_orient' => true,
                ],
            ],
        ]);

        $model->attachment = new SplFileInfo($this->getTestFilePath('empty.gif'));
        $model->save();

        $processedFilePath         = $this->getUploadedAttachmentPath($model, 'empty.gif');
        $processedFilePathVariantA = $this->getUploadedAttachmentPath($model, 'empty.gif', 'attachment', 'a');
        $processedFilePathVariantB = $this->getUploadedAttachmentPath($model, 'empty.gif', 'attachment', 'b');

        static::assertFileExists($processedFilePath, 'Original file not stored');
        static::assertFileExists($processedFilePathVariantA, 'Variant A not stored');
        static::assertFileExists($processedFilePathVariantB, 'Variant B not stored');

        $model->delete();
    }

    /**
     * @test
     */
    function it_accepts_stapler_config_keys_and_normalizes_them_to_paperclip()
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'url' => 'test/path/for-model',
        ]);

        $model->attachment = new SplFileInfo($this->getTestFilePath());

        static::assertEquals('test/path/for-model/original/source.txt', $model->attachment->variantPath());
    }

}
