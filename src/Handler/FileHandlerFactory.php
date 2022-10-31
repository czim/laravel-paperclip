<?php

declare(strict_types=1);

namespace Czim\Paperclip\Handler;

use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\FileHandling\Contracts\Variant\VariantProcessorInterface;
use Czim\FileHandling\Handler\FileHandler;
use Czim\FileHandling\Storage\Laravel\LaravelStorage;
use Czim\Paperclip\Contracts\FileHandlerFactoryInterface;
use Illuminate\Contracts\Filesystem\Cloud as CloudFilesystemContract;
use Illuminate\Contracts\Filesystem\Filesystem as FilesystemContract;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FileHandlerFactory implements FileHandlerFactoryInterface
{
    protected const LOCAL_DRIVER = 'local';

    public function create(?string $storage = null): FileHandlerInterface
    {
        $storage = $storage ?: $this->getStorageDisk();

        return new FileHandler(
            $this->makeStorage($storage ?? ''),
            $this->makeProcessor(),
        );
    }

    /**
     * @param string $disk
     * @return LaravelStorage
     */
    protected function makeStorage(string $disk): LaravelStorage
    {
        if (trim($disk) === '') {
            throw new RuntimeException(
                "Paperclip storage disk invalid or null, check your paperclip and filesystems configuration"
            );
        }

        if (! $this->isStorageDiskAvailable($disk)) {
            throw new RuntimeException(
                "Paperclip storage disk '{$disk}' is not configured! "
                . 'Add an entry for it under the filesystems.disks configuration key.'
            );
        }

        $isLocal = $this->isDiskLocal($disk);
        $baseUrl = $this->getBaseUrlForDisk($disk);

        return new LaravelStorage($this->getLaravelStorageInstance($disk), $isLocal, $baseUrl);
    }

    protected function makeProcessor(): VariantProcessorInterface
    {
        return app(VariantProcessorInterface::class);
    }

    /**
     * Returns whether a given disk alias is for default local storage.
     *
     * @param string $disk
     * @return bool
     */
    protected function isDiskLocal(string $disk): bool
    {
        return config("filesystems.disks.{$disk}.driver") === static::LOCAL_DRIVER;
    }

    /**
     * Returns the storage disk to use. If no paperclip storage is defined, the default storage is used.
     *
     * @return string|null
     */
    protected function getStorageDisk(): ?string
    {
        return config('paperclip.storage.disk')
            ?: config('filesystems.default');
    }

    /**
     * Checks whether the given storage driver is available.
     *
     * @param string $driver
     * @return bool
     */
    protected function isStorageDiskAvailable(string $driver): bool
    {
        return array_key_exists($driver, config('filesystems.disks', []));
    }

    /**
     * Returns the (external) base URL to use for a given storage disk.
     *
     * @param string $disk
     * @return string
     */
    protected function getBaseUrlForDisk(string $disk): string
    {
        $url = config("paperclip.storage.base-urls.{$disk}") ?: config("filesystems.disks.{$disk}.url");

        if (is_string($url)) {
            return $url;
        }

        // Attempt to get URL from cloud storage directly.
        try {
            $storage = $this->getLaravelStorageInstance($disk);

            if ($storage instanceof CloudFilesystemContract) {
                $url = $storage->url('.');
            }
        } catch (Throwable) {
            throw new RuntimeException("Could not determine base URL through Storage::url() for '{$disk}'");
        }

        if (is_string($url)) {
            return $url;
        }

        throw new RuntimeException("Could not determine base URL for storage disk '{$disk}'");
    }

    protected function getLaravelStorageInstance(string $disk): FilesystemContract|CloudFilesystemContract
    {
        return Storage::disk($disk);
    }
}
