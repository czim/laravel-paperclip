<?php

namespace Czim\Paperclip\Events;

use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Throwable;

class ProcessingExceptionEvent
{
    /**
     * @var Throwable
     */
    protected $exception;

    /**
     * @var StorableFileInterface
     */
    protected $source;

    /**
     * @var string
     */
    protected $variant;

    /**
     * @var array
     */
    protected $information;


    /**
     * @param Throwable             $exception
     * @param StorableFileInterface $source
     * @param string                $variant
     * @param array                 $information
     */
    public function __construct(
        Throwable $exception,
        StorableFileInterface $source,
        $variant,
        array $information
    ) {
        $this->exception   = $exception;
        $this->source      = $source;
        $this->variant     = $variant;
        $this->information = $information;
    }


    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function getSource(): StorableFileInterface
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getVariant()
    {
        return $this->variant;
    }

    public function getInformation(): array
    {
        return $this->information;
    }
}
