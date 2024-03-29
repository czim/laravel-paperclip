<?php

declare(strict_types=1);

namespace Czim\Paperclip\Attachment;

use Czim\Paperclip\Config\PaperclipConfig;
use Czim\Paperclip\Config\StaplerConfig;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentFactoryInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Illuminate\Database\Eloquent\Model;

class AttachmentFactory implements AttachmentFactoryInterface
{
    /**
     * @param Model&AttachableInterface $instance
     * @param string                    $name
     * @param array<string, mixed>      $config
     * @return AttachmentInterface
     */
    public function create(AttachableInterface $instance, string $name, array $config = []): AttachmentInterface
    {
        $attachment = $this->createInstance();

        $configObject = $this->makeConfigObject($config);

        $attachment->setInstance($instance);
        $attachment->setName($name);
        $attachment->setConfig($configObject);
        $attachment->setInterpolator($this->getInterpolator());
        $attachment->setStorage($configObject->storageDisk());

        return $attachment;
    }

    protected function createInstance(): AttachmentInterface
    {
        return new Attachment();
    }

    /**
     * @param array<string, mixed> $config
     * @return ConfigInterface
     */
    protected function makeConfigObject(array $config): ConfigInterface
    {
        if ($this->isStaplerConfigMode()) {
            return new StaplerConfig($config);
        }

        return new PaperclipConfig($config);
    }

    protected function isStaplerConfigMode(): bool
    {
        return strtolower(config('paperclip.config.mode') ?: '') === 'stapler';
    }

    protected function getInterpolator(): InterpolatorInterface
    {
        $interpolatorClass = config('paperclip.path.interpolator', InterpolatorInterface::class);

        return app($interpolatorClass);
    }
}
