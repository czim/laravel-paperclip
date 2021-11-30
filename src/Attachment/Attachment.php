<?php

namespace Czim\Paperclip\Attachment;

use Carbon\Carbon;
use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\FileHandling\Contracts\Handler\ProcessResultInterface;
use Czim\FileHandling\Contracts\Storage\StorableFileFactoryInterface;
use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\FileHandling\Contracts\Storage\TargetInterface;
use Czim\FileHandling\Handler\FileHandler;
use Czim\FileHandling\Handler\ProcessResult;
use Czim\Paperclip\Config\PaperclipConfig;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Contracts\Config\ConfigInterface;
use Czim\Paperclip\Contracts\FileHandlerFactoryInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Czim\Paperclip\Events\AttachmentSavedEvent;
use Czim\Paperclip\Events\ProcessingExceptionEvent;
use Czim\Paperclip\Events\TemporaryFileFailedToBeDeletedEvent;
use Czim\Paperclip\Exceptions\VariantProcessFailureException;
use Czim\Paperclip\Path\InterpolatingTarget;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Attachment implements AttachmentInterface
{
    const NULL_ATTACHMENT = '44e1ec68e2a43f32741cbd4cb4d77c79e28d6a5c';

    /**
     * @var AttachableInterface|Model
     */
    protected $instance;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var FileHandlerInterface
     */
    protected $handler;

    /**
     * @var string|null
     */
    protected $storage;

    /**
     * @var InterpolatorInterface
     */
    protected $interpolator;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Contents of the config file normalized to be used
     *
     * @var array
     */
    protected $normalizedConfig = [];

    /**
     * @var StorableFileInterface
     */
    protected $uploadedFile;

    /**
     * The uploaded/resized files that have been queued up for deletion.
     *
     * @var array
     */
    protected $queuedForDelete = [];

    /**
     * Whether the uploaded file is queued for processing/writing.
     *
     * @var bool
     */
    protected $queuedForWrite = false;

    /**
     * The target definition for file handling.
     *
     * @var null|TargetInterface
     */
    protected $target;

    /**
     * The target instance to be used for queued deletions.
     *
     * @var null|TargetInterface
     */
    protected $deleteTarget;


    public function __construct()
    {
        $this->config = new PaperclipConfig([]);
    }


    /**
     * Sets the underlying instance object.
     *
     * @param AttachableInterface $instance
     * @return $this
     */
    public function setInstance(AttachableInterface $instance)
    {
        $this->instance = $instance;

        $this->clearTarget();

        return $this;
    }

    /**
     * Returns the underlying instance (model) object for this attachment.
     *
     * @return AttachableInterface
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Returns the key for the underlying object instance.
     *
     * @return mixed
     */
    public function getInstanceKey()
    {
        return $this->instance->getKey();
    }

    /**
     * Returns the class type of the attachment's underlying object instance.
     *
     * @return string
     */
    public function getInstanceClass()
    {
        return get_class($this->instance);
    }

    /**
     * Sets the attachment (attribute) name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        $this->clearTarget();

        return $this;
    }

    /**
     * Returns the name (the attribute on the model) for the attachment.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @param InterpolatorInterface $interpolator
     * @return $this
     */
    public function setInterpolator(InterpolatorInterface $interpolator)
    {
        $this->interpolator = $interpolator;

        $this->clearTarget();

        return $this;
    }

    /**
     * Sets the configuration.
     *
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;

        $this->clearTarget();

        return $this;
    }

    /**
     * Returns the configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config->getOriginalConfig();
    }

    /**
     * Returns the configuration after normalization.
     *
     * @return array
     */
    public function getNormalizedConfig()
    {
        return $this->config->toArray();
    }

    /**
     * Sets the storage disk identifier.
     *
     * @param string $storage   disk identifier
     * @return $this
     */
    public function setStorage($storage)
    {
        if ($this->storage !== $storage || ! $this->handler) {

            $this->storage = $storage;

            $this->handler = $this->getFileHandlerFactory()->create($storage);
        }

        return $this;
    }

    /**
     * Returns the storage disk used by the attachment.
     *
     * @return null|string
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return FileHandlerInterface
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Sets a file to be processed and stored.
     *
     * This is not done instantly. Rather, the file is queued for processing when the model is saved.
     *
     * @param StorableFileInterface $file
     */
    public function setUploadedFile(StorableFileInterface $file)
    {
        $this->instance->markAttachmentUpdated();

        if ( ! $this->config->keepOldFiles()) {
            $this->clear();
        }

        $this->clearTarget();

        $this->uploadedFile = $file;

        // Update the model instance with appropriate column values
        $this->instanceWrite('file_name', $this->uploadedFile->name());
        $this->instanceWrite('file_size', $this->uploadedFile->size());
        $this->instanceWrite('content_type', $this->uploadedFile->mimeType());
        $this->instanceWrite('updated_at', Carbon::now());

        $this->performCallableHookBeforeProcessing();

        $this->queueAllForWrite();
    }

    /**
     * Sets the attachment to be deleted.
     *
     * This does NOT override the preserve-files config option.
     */
    public function setToBeDeleted()
    {
        $this->instance->markAttachmentUpdated();

        if ( ! $this->config->keepOldFiles()) {
            $this->clear();
        }

        $this->clearAttributes();
    }

    /**
     * Reprocesses variants from the currently set original file.
     *
     * @param array $variants   ['*'] for all
     * @throws VariantProcessFailureException
     */
    public function reprocess($variants = ['*'])
    {
        if ( ! $this->exists()) {
            return;
        }

        // There is no need to change the original here (that would even be unnecessarily risky),
        // so we just need to reprocess each variant separately, based on the original content.
        if (in_array('*', $variants)) {
            $variants = $this->variants();
        }

        $source = $this->getStorableFileFactory()->makeFromUrl(
            $this->url(),
            $this->originalFilename(),
            $this->contentType()
        );

        // Collect information about variants to update the variant information with after processing.
        $variantInformation = [];

        $temporaryFiles = [];

        foreach ($variants as $variant) {

            $result = $this->processSingleVariant($source, $variant, $variantInformation);

            foreach ($result->temporaryFiles() as $temporaryFile) {
                $temporaryFiles[] = $temporaryFile;
            }
        }

        $this->cleanUpTemporaryFiles($temporaryFiles);


        if ($this->shouldVariantInformationBeStored()) {
            $this->instanceWrite('variants', json_encode($variantInformation));
            $this->instance->save();
        }
    }

    /**
     * Returns list of keys for defined variants.
     *
     * @param bool $withOriginal
     * @return string[]
     */
    public function variants($withOriginal = false)
    {
        $variants = array_keys($this->config->variantConfigs());

        if ($withOriginal && ! in_array(FileHandler::ORIGINAL, $variants)) {
            array_unshift($variants, FileHandler::ORIGINAL);
        }

        return $variants;
    }

    /**
     * Generates the url to an uploaded file (or a variant).
     *
     * @param string $variant
     * @return string|null
     */
    public function url($variant = null)
    {
        $variant = $variant ?: FileHandler::ORIGINAL;

        // If no attached file exists, we may return null or give a fallback URL.
        if ( ! $this->exists()) {
            return $this->config->defaultVariantUrl($variant);
        }

        $target = $this->getOrMakeTargetInstance();

        return Arr::get(
            $this->handler->variantUrlsForTarget($target, [ $variant ]),
            $variant
        );
    }

    /**
     * Returns the relative base storage path.
     *
     * @return string
     */
    public function path()
    {
        return $this->getOrMakeTargetInstance()->original();
    }

    /**
     * Returns the relative storage path for a variant.
     *
     * @param string|null $variant
     * @return string|null
     */
    public function variantPath($variant = null)
    {
        $variant = $variant ?: FileHandler::ORIGINAL;

        $target = $this->getOrMakeTargetInstance();

        if ($variant == FileHandler::ORIGINAL) {
            return $target->original();
        }

        return $target->variant($variant);
    }

    /**
     * Returns the filename for a given variant.
     *
     * @param string|null $variant
     * @return string
     */
    public function variantFilename($variant)
    {
        if (null === $variant || ! ($extension = $this->variantExtension($variant))) {
            return $this->originalFilename();
        }

        return pathinfo($this->originalFilename(), PATHINFO_FILENAME) . ".{$extension}";
    }

    /**
     * Returns the extension for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantExtension($variant)
    {
        $variants = $this->variantsAttribute();

        if ( ! empty($variants)) {
            return Arr::get($variants, "{$variant}.ext") ?: false;
        }

        return $this->config->variantExtension($variant);
    }

    /**
     * Returns the mimeType for a given variant.
     *
     * @param string $variant
     * @return string|false
     */
    public function variantContentType($variant)
    {
        $variants = $this->variantsAttribute();

        if ( ! empty($variants)) {
            return Arr::get($variants, "{$variant}.type") ?: false;
        }

        if (false !== ($type = $this->config->variantMimeType($variant))) {
            return $type;
        }

        return $this->contentType();
    }

    public function jsonSerialize(): mixed
    {
        // @codeCoverageIgnoreStart
        if ( ! $this->originalFilename()) {
            return [];
        }
        // @codeCoverageIgnoreEnd

        $data = [];

        foreach ($this->variants(true) as $variant) {
            $data[ $variant ] = [
                'path' => $this->variantPath($variant),
                'url'  => $this->url($variant)
            ];
        }

        return $data;
    }

    /**
     * Returns the target instance to be passed to the file handler.
     *
     * @return TargetInterface
     */
    protected function getOrMakeTargetInstance()
    {
        if ($this->target) {
            return $this->target;
        }

        $this->target = new InterpolatingTarget(
            $this->interpolator,
            $this,
            $this->config->path(),
            $this->config->variantPath()
        );

        $this->target->setVariantFilenames($this->variantFilenames());
        $this->target->setVariantExtensions($this->variantExtensions());

        return $this->target;
    }

    /**
     * Returns a target instance with fixed historical data for the current state.
     *
     * @return TargetInterface
     */
    protected function makeTargetInstanceWithCurrentData()
    {
        $this->target = new InterpolatingTarget(
            $this->interpolator,
            $this->getCurrentAttachmentData(),
            $this->config->path(),
            $this->config->variantPath()
        );

        $this->target->setVariantFilenames($this->variantFilenames());
        $this->target->setVariantExtensions($this->variantExtensions());

        return $this->target;
    }

    /**
     * Clears the currently cached target instance.
     */
    protected function clearTarget()
    {
        $this->target = null;
    }

    /**
     * Returns filenames keyed by variant.
     *
     * @return string[]
     */
    protected function variantFilenames()
    {
        $names = [];

        foreach ($this->variants() as $variant) {
            $names[ $variant ] = $this->variantFilename($variant);
        }

        return $names;
    }

    /**
     * Returns alternative extensions keyed by variant.
     *
     * @return string[]
     */
    protected function variantExtensions()
    {
        $extensions = $this->config->variantExtensions();

        $variants = $this->variantsAttribute();

        if ( ! empty($variants)) {

            foreach ($this->variants() as $variant) {

                $extension = Arr::get($variants, "{$variant}.ext");

                if ($extension) {
                    $extensions[ $variant ] = $extension;
                    continue;
                }
            }
        }

        return $extensions;
    }


    // ------------------------------------------------------------------------------
    //      Model Hooks
    // ------------------------------------------------------------------------------

    /**
     * Processes the write queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterSave(AttachableInterface $instance)
    {
        $this->instance = $instance;
        $this->save();

        $this->performCallableHookAfterProcessing();
    }

    /**
     * Queues up this attachments files for deletion.
     *
     * @param AttachableInterface $instance
     */
    public function beforeDelete(AttachableInterface $instance)
    {
        $this->instance = $instance;

        if ( ! $this->config->preserveFiles()) {
            $this->clear();
        }
    }

    /**
     * Processes the delete queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterDelete(AttachableInterface $instance)
    {
        $this->instance = $instance;
        $this->flushDeletes();
    }


    // ------------------------------------------------------------------------------
    //      Updates/Deletes & Queueing
    // ------------------------------------------------------------------------------

    /**
     * Flushes the queuedForDeletion and queuedForWrite arrays.
     */
    public function save()
    {
        $this->flushDeletes();
        $this->flushWrites();
    }

    /**
     * Removes all uploaded files (from storage) for this attachment.
     *
     * This method does not clear out attachment attributes on the model instance.
     *
     * @param array $variants
     */
    public function destroy(array $variants = [])
    {
        $this->clear($variants);
        $this->flushDeletes();
    }

    /**
     * Queues up all or some of this attachments uploaded files/images for deletion.
     *
     * @param array $variants   clear only specific variants
     */
    protected function clear(array $variants = [])
    {
        if ($variants) {
            $this->queueSomeForDeletion($variants);
            return;
        }

        $this->queueAllForDeletion();
    }

    /**
     * Process the queuedForWrite queue.
     */
    protected function flushWrites()
    {
        if ( ! $this->queuedForWrite) {
            return;
        }

        $target = $this->getOrMakeTargetInstance();

        $result = $this->handler->process($this->uploadedFile, $target, $this->config->toArray());

        $storedFiles = $result->storedFiles();

        if ($this->shouldVariantInformationBeStored()) {

            $originalExtension  = pathinfo($this->originalFilename(), PATHINFO_EXTENSION);
            $originalMimeType   = $this->contentType();
            $variantInformation = [];

            foreach ($storedFiles as $variant => $storedFile) {
                if (    $storedFile->extension() == $originalExtension
                    &&  $storedFile->mimeType() == $originalMimeType
                ) {
                    continue;
                }

                $variantInformation[ $variant ] = [
                    'ext'  => $storedFile->extension(),
                    'type' => $storedFile->mimeType(),
                ];
            }

            $this->instanceWrite('variants', json_encode($variantInformation));
            $this->instance->save();
        }


        $this->cleanUpTemporaryFiles($result->temporaryFiles());

        $this->queuedForWrite = false;

        $event = new AttachmentSavedEvent($this, $this->uploadedFile);
        $this->getEventDispatcher()->dispatch($event);
    }

    /**
     * @param StorableFileInterface[] $temporaryFiles
     */
    protected function cleanUpTemporaryFiles(array $temporaryFiles)
    {
        foreach ($temporaryFiles as $temporaryFile) {

            try {
                $temporaryFile->delete();

            } catch (Exception $e) {

                // Paperclip itself ignores problems while deleting temporary files.
                // If you want to handle these errors yourself, you can set up a
                // listener for the event fired here.

                $this->getEventDispatcher()->dispatch(
                    new TemporaryFileFailedToBeDeletedEvent($temporaryFile, $e)
                );
            }
        }
    }

    /**
     * Process the queuedForDeletion queue.
     */
    protected function flushDeletes()
    {
        if ( ! $this->deleteTarget) {
            return;
        }

        foreach ($this->queuedForDelete as $variant) {
            $this->handler->deleteVariant($this->deleteTarget, $variant);
        }

        $this->queuedForDelete = [];
    }

    /**
     * @param StorableFileInterface $source
     * @param string                $variant
     * @param array                 $information
     * @return ProcessResultInterface
     * @throws VariantProcessFailureException
     */
    protected function processSingleVariant(StorableFileInterface $source, $variant, array &$information = [])
    {
        $target = $this->getOrMakeTargetInstance();

        try {
            $result = $this->handler->processVariant(
                $source,
                $target,
                $variant,
                $this->config->variantConfig($variant)
            );
        } catch (Exception $e) {

            if ($this->shouldFireEventsForExceptions()) {

                $this->getEventDispatcher()->dispatch(
                    new ProcessingExceptionEvent($e, $source, $variant, $information)
                );

                return new ProcessResult([], []);
            }

            throw new VariantProcessFailureException(
                "Failed to process variant '{$variant}': {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }

        /** @var StorableFileInterface $storedFile */
        $storedFile = array_values($result->storedFiles())[0];

        if ( ! $this->shouldVariantInformationBeStored()) {
            return $result;
        }

        $originalExtension = pathinfo($this->originalFilename(), PATHINFO_EXTENSION);
        $originalMimeType  = $this->contentType();

        if (    $storedFile->extension() == $originalExtension
            &&  $storedFile->mimeType() == $originalMimeType
        ) {
            return $result;
        }

        $information[ $variant ] = [
            'ext'  => $storedFile->extension(),
            'type' => $storedFile->mimeType(),
        ];

        return $result;
    }

    /**
     * Fill the queuedForWrite queue with all of this attachment's styles.
     */
    protected function queueAllForWrite()
    {
        $this->queuedForWrite = true;
    }

    /**
     * Add a subset (filtered by variant name) of the uploaded files
     * for this attachment to the queuedForDeletion queue.
     *
     * @param array $variants
     */
    protected function queueSomeForDeletion(array $variants)
    {
        $this->deleteTarget    = $this->makeTargetInstanceWithCurrentData();
        $this->queuedForDelete = array_unique(array_merge($this->queuedForDelete, $variants));
    }

    /**
     * Add all uploaded files (across all image styles) to the queuedForDeletion queue.
     */
    protected function queueAllForDeletion()
    {
        // If no file is currently stored, don't delete anything.
        if ( ! $this->originalFilename()) {
            return;
        }

        $this->deleteTarget    = $this->makeTargetInstanceWithCurrentData();
        $this->queuedForDelete = array_unique(array_merge($this->queuedForDelete, $this->variants(true)));
    }

    /**
     * Returns the current attachment state data.
     *
     * @return AttachmentData
     */
    protected function getCurrentAttachmentData()
    {
        $attributes = [
            'file_name'    => $this->originalFilename(),
            'file_size'    => $this->size(),
            'content_type' => $this->contentType(),
            'updated_at'   => $this->updatedAt(),
            'created_at'   => $this->createdAt(),
            'variants'     => $this->variantsAttribute(),
        ];

        $variants = [];

        foreach ($this->variants() as $variant) {

            $variants[ $variant ] = [
                'file_name'    => $this->variantFilename($variant),
                'content_type' => $this->variantContentType($variant),
                'extension'    => $this->variantExtension($variant),
            ];
        }

        return new AttachmentData(
            $this->name,
            $this->getConfig(),
            $attributes,
            $variants,
            $this->getInstanceKey(),
            $this->getInstanceClass()
        );
    }


    // ------------------------------------------------------------------------------
    //      Uploaded File Properties
    // ------------------------------------------------------------------------------

    /**
     * Returns whether this attachment actually has a file currently stored.
     *
     * @return bool
     */
    public function exists()
    {
        return ! empty($this->originalFilename());
    }

    /**
     * Returns the creation time of the file as originally assigned to this attachment's model.
     *
     * Lives in the <attachment>_created_at attribute of the model.
     * This attribute may conditionally exist on the model, it is not one of the four required fields.
     *
     * @return string
     */
    public function createdAt()
    {
        return $this->instance->getAttribute("{$this->name}_created_at");
    }

    /**
     * Returns the last modified time of the file as originally assigned to this attachment's model.
     *
     * Lives in the <attachment>_updated_at attribute of the model.
     *
     * @return string
     */
    public function updatedAt()
    {
        return $this->instance->getAttribute("{$this->name}_updated_at");
    }

    /**
     * Returns the content type of the file as originally assigned to this attachment's model.
     *
     * Lives in the <attachment>_content_type attribute of the model.
     *
     * @return string
     */
    public function contentType()
    {
        return $this->instance->getAttribute("{$this->name}_content_type");
    }

    /**
     * Returns the size of the file as originally assigned to this attachment's model.
     *
     * Lives in the <attachment>_file_size attribute of the model.
     *
     * @return int
     */
    public function size()
    {
        return $this->instance->getAttribute("{$this->name}_file_size");
    }

    /**
     * Returns the name of the file as originally assigned to this attachment's model.
     *
     * Lives in the <attachment>_file_name attribute of the model.
     *
     * @return string
     */
    public function originalFilename()
    {
        return $this->instance->getAttribute("{$this->name}_file_name");
    }

    /**
     * Returns the JSON information stored on the model about variants as an associative array.
     *
     * @return array
     */
    public function variantsAttribute()
    {
        if ( ! $this->shouldVariantInformationBeStored()) {
            return [];
        }

        return json_decode($this->instance->getAttribute("{$this->name}_variants"), true) ?: [];
    }

    /**
     * Clears all attachment related model attributes.
     */
    public function clearAttributes()
    {
        $this->instanceWrite('file_name', null);
        $this->instanceWrite('file_size', null);
        $this->instanceWrite('content_type', null);
        $this->instanceWrite('updated_at', null);
        $this->instanceWrite('variants', null);
    }

    /**
     * Sets an attachment property on the model instance.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function instanceWrite($property, $value)
    {
        // Ignore properties that should not be settable
        if ($property !== 'file_name' && ! $this->config->attributeProperty($property)) {
            return;
        }

        $this->instance->setAttribute("{$this->name}_{$property}", $value);
    }


    // ------------------------------------------------------------------------------
    //      Hooks
    // ------------------------------------------------------------------------------

    /**
     * Performs the hook callable before processing if configured.
     *
     * @return bool
     */
    protected function performCallableHookBeforeProcessing()
    {
        return $this->performCallableHook('before');
    }

    /**
     * Performs the hook callable after processing if configured.
     *
     * @return bool
     */
    protected function performCallableHookAfterProcessing()
    {
        return $this->performCallableHook('after');
    }

    /**
     * @param string $type  'before', 'after'
     * @return bool
     */
    protected function performCallableHook($type)
    {
        switch ($type) {

            case 'before':
                $hook = $this->config->beforeCallable();
                break;

            case 'after':
                $hook = $this->config->afterCallable();
                break;

            default:
                throw new \UnexpectedValueException("Unknown callable type '{$type}'");
        }

        if ( ! $hook) {
            return true;
        }

        $result = true;

        if (is_callable($hook)) {
            $result = $hook($this);
        } elseif (is_string($hook)) {
            $result = $this->performStringCallable($hook);
        }

        return $result === null ? true : (bool) $result;
    }

    /**
     * Performs callable defined as Class::method
     *
     * @param string $callable
     * @return bool
     */
    protected function performStringCallable($callable)
    {
        if ( ! preg_match('#^(?<class>[\\a-z0-9_]+)@(?<method>[a-z0-9_]+)$#i', $callable, $matches)) {
            throw new \UnexpectedValueException("Unable to process callable string '{$callable}'");
        }

        $instance = app($matches['class']);

        $result = $instance->{$matches['method']}($this);

        return $result === null ? true : (bool) $result;
    }


    /**
     * If we're writing variants, log information about the variants,
     * if the model is set up and configured to use the variants attribute.
     *
     * @return bool
     */
    protected function shouldVariantInformationBeStored()
    {
        return (bool) $this->config->variantsAttribute();
    }

    /**
     * @return bool
     */
    protected function shouldFireEventsForExceptions()
    {
        return (bool) config('paperclip.processing.errors.events', true);
    }

    /**
     * @return StorableFileFactoryInterface
     */
    protected function getStorableFileFactory()
    {
        return app(StorableFileFactoryInterface::class);
    }

    /**
     * @return FileHandlerFactoryInterface
     */
    protected function getFileHandlerFactory()
    {
        return app(FileHandlerFactoryInterface::class);
    }

    /**
     * @return \Illuminate\Contracts\Events\Dispatcher
     */
    protected function getEventDispatcher()
    {
        return app('events');
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        // Serialize everything that is unlikely to involve closures
        return [
            'instance'         => $this->instance,
            'storage'          => $this->storage,
            'name'             => $this->name,
            'interpolator'     => $this->interpolator,
            'config'           => $this->config,
            'normalizedConfig' => $this->normalizedConfig,
            'uploadedFile'     => $this->uploadedFile,
            'queuedForDelete'  => $this->queuedForDelete,
            'queuedForWrite'   => $this->queuedForWrite,
            'target'           => $this->target,
            'deleteTarget'     => $this->deleteTarget,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        $this->instance         = $data['instance'];
        $this->name             = $data['name'];
        $this->interpolator     = $data['interpolator'];
        $this->config           = $data['config'];
        $this->normalizedConfig = $data['normalizedConfig'];
        $this->uploadedFile     = $data['uploadedFile'];
        $this->queuedForDelete  = $data['queuedForDelete'];
        $this->queuedForWrite   = $data['queuedForWrite'];
        $this->target           = $data['target'];
        $this->deleteTarget     = $data['deleteTarget'];

        $this->setStorage($data['storage']);
    }
}
