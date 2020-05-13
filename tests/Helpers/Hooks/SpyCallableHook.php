<?php

namespace Czim\Paperclip\Test\Helpers\Hooks;

use Czim\Paperclip\Contracts\AttachmentInterface;

class SpyCallableHook
{
    public $hookMethodCalled = false;


    public function hookMethod(AttachmentInterface $attachment): void
    {
        $this->hookMethodCalled = true;
    }
}
