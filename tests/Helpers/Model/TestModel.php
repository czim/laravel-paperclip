<?php
namespace Czim\Paperclip\Test\Helpers\Model;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Model\PaperclipTrait;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model implements AttachableInterface
{
    use PaperclipTrait;

    protected $fillable = [
        'name',
        'attachment',
    ];


}
