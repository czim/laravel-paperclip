<?php

namespace Czim\Paperclip\Events;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Exception;

class TemporaryFileFailedToBeDeletedEvent
{

    /**
     * @var StorableFileInterface
     */
    protected $file;

    /**
     * @var Exception|null
     */
    protected $exception;


    public function __construct(StorableFileInterface $file, Exception $exception = null)
    {
        $this->file      = $file;
        $this->exception = $exception;
    }


    /**
     * @return StorableFileInterface
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return Exception|null
     */
    public function getException()
    {
        return $this->exception;
    }

}
