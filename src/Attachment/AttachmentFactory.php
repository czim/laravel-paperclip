<?php

namespace Czim\Paperclip\Attachment;

use Czim\Paperclip\Config\PaperclipConfig;
use Czim\Paperclip\Config\StaplerConfig;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentFactoryInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;

class AttachmentFactory implements AttachmentFactoryInterface
{

    /**
     * @param AttachableInterface $instance
     * @param string              $name
     * @param array               $config
     * @return AttachmentInterface
     */
    public function create(AttachableInterface $instance, $name, array $config = [])
    {
        $attachment = new Attachment;

        $configObject = $this->makeConfigObject($config);

        $attachment->setInstance($instance);
        $attachment->setName($name);
        $attachment->setConfig($configObject);
        $attachment->setInterpolator($this->getInterpolator());
        $attachment->setStorage($configObject->storageDisk());

        return $attachment;
    }

    /**
     * @param array $config
     * @return ConfigInterface
     */
    protected function makeConfigObject(array $config)
    {
        if (config('paperclip.config.mode') === 'stapler') {
            return new StaplerConfig($config);
        }

        return new PaperclipConfig($config);
    }

    /**
     * @return InterpolatorInterface
     */
    protected function getInterpolator()
    {
        $interpolatorClass = config('paperclip.path.interpolator', InterpolatorInterface::class);

        return app($interpolatorClass);
    }
}
