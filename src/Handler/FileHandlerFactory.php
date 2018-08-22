<?php
namespace Czim\Paperclip\Handler;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\FileHandling\Contracts\Variant\VariantProcessorInterface;
use Czim\FileHandling\Handler\FileHandler;
use Czim\FileHandling\Storage\Laravel\LaravelStorage;
use Czim\Paperclip\Contracts\FileHandlerFactoryInterface;
use Storage;

class FileHandlerFactory implements FileHandlerFactoryInterface
{

    /**
     * Makes a file handler instance.
     *
     * @param string|null $storage
     * @return FileHandlerInterface
     */
    public function create($storage = null)
    {
        $storage = $storage ?: config('paperclip.storage.disk', 'paperclip');

        return new FileHandler(
            $this->makeStorage($storage),
            $this->makeProcessor()
        );
    }

    /**
     * @param string $disk
     * @return LaravelStorage
     */
    protected function makeStorage($disk)
    {
        $isLocal = 'local' === config("filesystems.disks.{$disk}.driver");
        $baseUrl = config(
            "paperclip.storage.base-urls.{$disk}",
            config("filesystems.disks.{$disk}.url", url())
        );

        return new LaravelStorage(Storage::disk($disk), $isLocal, $baseUrl);
    }

    /**
     * @return VariantProcessorInterface
     */
    protected function makeProcessor()
    {
        return app(VariantProcessorInterface::class);
    }

}
