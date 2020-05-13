<?php

namespace Czim\Paperclip\Test\Helpers\Model;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string                   $name
 * @property AttachmentInterface|null $attachment
 * @property string                   $attachment_file_name
 * @property integer                  $attachment_file_size
 * @property string                   $attachment_content_type
 * @property string                   $attachment_updated_at
 * @property array|null               attachment_variants
 * @property AttachmentInterface|null $image
 * @property string                   $image_file_name
 * @property integer                  $image_file_size
 * @property string                   $image_content_type
 * @property string                   $image_created_at
 * @property string                   $image_updated_at
 * @property \Carbon\Carbon           $updated_at
 * @property \Carbon\Carbon           $created_at
 */
class TestModel extends Model implements AttachableInterface
{
    use PaperclipTrait;

    protected $fillable = [
        'name',
        'attachment',
        'image',
    ];

    public function __construct(array $attributes = [], array $attachmentConfig = [])
    {
        $this->hasAttachedFile('attachment', $attachmentConfig);

        $this->hasAttachedFile('image', [
            'variants'  => [
                'medium'   => [
                    'resize' => [
                        'dimensions' => '300x300',
                    ],
                ],
            ],
        ]);

        parent::__construct($attributes);
    }
}
