<?php

declare(strict_types=1);

namespace Czim\Paperclip\Test\Helpers\Hooks;

use Czim\Paperclip\Contracts\AttachmentInterface;

class SpyCallableHook
{
    public bool $hookMethodCalled = false;

    public function hookMethod(AttachmentInterface $attachment): void
    {
        $this->hookMethodCalled = true;
    }
}
