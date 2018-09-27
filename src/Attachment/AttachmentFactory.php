<?php
namespace Czim\Paperclip\Attachment;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentFactoryInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
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

        $attachment->setInstance($instance);
        $attachment->setName($name);
        $attachment->setConfig($config);
        $attachment->setInterpolator($this->getInterpolator());

        $disk = array_get($config, 'storage');
        $attachment->setStorage($disk);

        return $attachment;
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
