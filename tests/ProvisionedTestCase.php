<?php

namespace Czim\Paperclip\Test;

use Illuminate\Database\Eloquent\Model;

abstract class ProvisionedTestCase extends TestCase
{

    protected function getTestFilePath(string $file = 'source.txt'): string
    {
        return realpath(__DIR__ . '/resources/' . $file);
    }

    protected function getBasePaperclipPath(): string
    {
        return $this->getBasePath() . '/public/paperclip/';
    }

    protected function getUploadedAttachmentPath(
        Model $model,
        string $file = 'source.txt',
        string $attachmentName = 'attachment',
        string $variant = 'original'
    ): string {
        return $this->getBasePaperclipPath()
            . str_replace('\\', '/', get_class($model))
            . '/000/000/' . (str_pad((string) $model->getKey(), 3, '0', STR_PAD_LEFT))
            . '/' . $attachmentName
            . '/' . $variant
            . '/' . $file;
    }
}
