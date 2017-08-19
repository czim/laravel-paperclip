<?php
namespace Czim\Paperclip\Test;

use Illuminate\Database\Eloquent\Model;

abstract class ProvisionedTestCase extends TestCase
{

    /**
     * @param string $file
     * @return string
     */
    protected function getTestFilePath($file = 'source.txt')
    {
        return realpath(__DIR__ . '/resources/' . $file);
    }

    /**
     * @return string
     */
    protected function getBasePaperclipPath()
    {
        return $this->getBasePath() . '/public/paperclip/';
    }

    /**
     * @param Model  $model
     * @param string $file
     * @param string $attachmentName
     * @param string $variant
     * @return string
     */
    protected function getUploadedAttachmentPath(
        Model $model,
        $file = 'source.txt',
        $attachmentName = 'attachment',
        $variant = 'original'
    ) {
        return $this->getBasePaperclipPath()
            . str_replace('\\', '/', get_class($model))
            . '/000/000/' . (str_pad((string) $model->getKey(), 3, '0', STR_PAD_LEFT))
            . '/' . $attachmentName
            . '/' . $variant
            . '/' . $file;
    }

}
