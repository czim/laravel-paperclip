<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace Czim\Paperclip\Test\Integration;

use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Config\StaplerConfig;
use Czim\Paperclip\Test\ProvisionedTestCase;
use SplFileInfo;

class PaperclipAttachmentStaplerCompatibilityTest extends ProvisionedTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Enable stapler fallback interpretation through config.
        $this->app['config']->set('paperclip.config.mode', 'stapler');
    }


    /**
     * @test
     */
    public function it_uses_stapler_styles_key_for_variants(): void
    {
        $attachment = new Attachment;
        $attachment->setConfig(new StaplerConfig([
            'styles' => [
                'some'    => '100x100',
                'variant' => '50x30',
                'keys'    => '40x',
            ],
        ]));

        static::assertEquals(['some', 'variant', 'keys'], $attachment->variants());
    }

    /**
     * @test
     */
    public function it_accepts_stapler_styles_and_resizes_configuration(): void
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

        $model->setAttribute('attachment', new SplFileInfo($this->getTestFilePath('empty.gif')));
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
    public function it_accepts_stapler_config_keys_and_normalizes_them_to_paperclip(): void
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'url' => 'test/path/for-model/original/:filename',
        ]);

        $model->setAttribute('attachment', new SplFileInfo($this->getTestFilePath()));

        static::assertEquals('test/path/for-model/original/source.txt', $model->attachment->variantPath());
    }

    /**
     * @test
     */
    public function its_attachments_return_normalized_config(): void
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'url'            => 'test/path/for-model',
            'preserve_files' => true,
            'keep_old_files' => true,
            'styles'         => [
                'a' => '50x50',
                'b' => [
                    'dimensions'  => '40x40',
                    'auto_orient' => true,
                ],
            ],
        ]);

        $config = $model->attachment->getNormalizedConfig();

        static::assertArrayHasKey('path', $config);
        static::assertArrayNotHasKey('url', $config);

        static::assertArrayHasKey('preserve-files', $config);
        static::assertArrayNotHasKey('preserve_files', $config);

        static::assertArrayHasKey('keep-old-files', $config);
        static::assertArrayNotHasKey('keep_old_files', $config);

        static::assertArrayHasKey('variants', $config);
        static::assertArrayNotHasKey('styles', $config);

        static::assertArrayHasKey('a', $config['variants']);
        static::assertArrayHasKey('resize', $config['variants']['a'], 'Resize step was not extracted');
        static::assertArrayHasKey('b', $config['variants']);
        static::assertArrayHasKey('auto-orient', $config['variants']['b'], 'Auto orient step was not extracted');
        static::assertArrayHasKey('resize', $config['variants']['b'], 'Resize step was not extracted');
    }
}
