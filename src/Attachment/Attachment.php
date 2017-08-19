<?php
namespace Czim\Paperclip\Attachment;

use Carbon\Carbon;
use Czim\FileHandling\Contracts\Handler\FileHandlerInterface;
use Czim\FileHandling\Contracts\Storage\PathHelperInterface;
use Czim\FileHandling\Contracts\Storage\StorableFileInterface;
use Czim\FileHandling\Handler\FileHandler;
use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Illuminate\Database\Eloquent\Model;

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
     * @var InterpolatorInterface
     */
    protected $interpolator;

    /**
     * @var PathHelperInterface
     */
    protected $pathHelper;

    /**
     * @var array
     */
    protected $config = [];

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
     * Sets the underlying instance object.
     *
     * @param AttachableInterface $instance
     * @return $this
     */
    public function setInstance(AttachableInterface $instance)
    {
        $this->instance = $instance;

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

        return $this;
    }

    /**
     * @param PathHelperInterface $helper
     * @return $this
     */
    public function setPathHelper(PathHelperInterface $helper)
    {
        $this->pathHelper = $helper;

        return $this;
    }

    /**
     * Sets the configuration.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        $this->normalizedConfig = $this->normalizeConfig();

        return $this;
    }

    /**
     * Returns the configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param FileHandlerInterface $handler
     * @return $this
     */
    public function setHandler(FileHandlerInterface $handler)
    {
        $this->handler = $handler;

        return $this;
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
        if ( ! $this->getConfigValue('keep-old-files')) {
            $this->clear();
        }

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
        if ( ! $this->getConfigValue('keep-old-files')) {
            $this->clear();
        }

        $this->clearAttributes();
    }

    /**
     * Returns list of keys for defined variants.
     *
     * @return string[]
     */
    public function variants()
    {
        return array_keys($this->getConfigValue('variants', []));
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

        return array_get(
            $this->handler->variantUrlsForBasePath($this->path(), $this->variantFilename($variant), [ $variant ]),
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
        return $this->interpolator->interpolate($this->getConfigValue('path'), $this);
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

        return $this->pathHelper->addVariantToBasePath($this->path(), $variant)
             . '/' . $this->variantFilename($variant);
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
            return array_get($variants, "{$variant}.ext") ?: false;
        }

        return $this->getConfigValue("extensions.{$variant}", false);
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
            return array_get($variants, "{$variant}.type") ?: false;
        }

        if (false !== ($type = $this->getConfigValue("types.{$variant}", false))) {
            return $type;
        }

        return $this->contentType();
    }

    /**
     * Return a JSON representation of this class.
     *
     * @return array
     */
    public function jsonSerialize()
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


    // ------------------------------------------------------------------------------
    //      Model Hooks
    // ------------------------------------------------------------------------------

    /**
     * Processes the write queue.
     *
     * @param AttachableInterface $instance
     */
    public function beforeSave(AttachableInterface $instance)
    {
        $this->instance = $instance;
        $this->save();
    }

    /**
     * Processes the write queue.
     *
     * @param AttachableInterface $instance
     */
    public function afterSave(AttachableInterface $instance)
    {
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

        if ( ! $this->getConfigValue('preserve-files')) {
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
    public function clear(array $variants = [])
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
        if ($this->queuedForWrite) {
            $storedFiles = $this->handler->process($this->uploadedFile, $this->path(), $this->normalizedConfig);

            // If we're writing variants, log information about the variants,
            // if the model is set up and configured to use the variants attribute.
            if ($this->getConfigValue('attributes.variants')) {

                $originalExtension = pathinfo($this->originalFilename(), PATHINFO_EXTENSION);
                $originalMimeType  = $this->contentType();
                $variantInformation = [];

                foreach ($storedFiles as $variant => $storedfile) {
                    if (    $storedfile->extension() == $originalExtension
                        &&  $storedfile->mimeType() == $originalMimeType
                    ) {
                        continue;
                    }

                    $variantInformation[ $variant ] = [
                        'ext'  => $storedfile->extension(),
                        'type' => $storedfile->mimeType(),
                    ];
                }

                $this->instanceWrite('variants', json_encode($variantInformation));
            }
        }

        $this->queuedForWrite = false;
    }

    /**
     * Process the queuedForDeletion queue.
     */
    protected function flushDeletes()
    {
        foreach ($this->queuedForDelete as $path) {
            $this->handler->deleteVariant($path);
        }

        $this->queuedForDelete = [];
    }

    /**
     * Fill the queuedForWrite queue with all of this attachment's styles.
     */
    protected function queueAllForWrite()
    {
        $this->queuedForWrite = true;
    }

    /**
     * Add a subset (filtered via style) of the uploaded files for this attachment
     * to the queuedForDeletion queue.
     *
     * @param array $variants
     */
    protected function queueSomeForDeletion(array $variants)
    {
        $paths = array_map(
            function ($variant) {
                return $this->variantPath($variant);
            },
            $variants
        );

        $this->queuedForDelete = array_unique(array_merge($this->queuedForDelete, $paths));
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

        $paths = array_map(
            function ($variant) {
                return $this->pathHelper->addVariantToBasePath($this->path(), $variant)
                     . '/' . $this->originalFilename();
            },
            array_merge($this->variants(), [ FileHandler::ORIGINAL ])
        );

        $this->queuedForDelete = array_unique(array_merge($this->queuedForDelete, $paths));
    }


    // ------------------------------------------------------------------------------
    //      Uploaded File Properties
    // ------------------------------------------------------------------------------

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
        if ( ! $this->getConfigValue('attributes.variants')) {
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
        if ($property !== 'file_name' && ! $this->getConfigValue("attributes.{$property}", true)) {
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
     * @param string $type
     * @return bool
     */
    protected function performCallableHook($type)
    {
        $hook = $this->getConfigValue($type);

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
        if ( ! preg_match('#^(?<class>[\\a-z0-9_]+)::(?<method>[a-z0-9_]+)$#i', $callable, $matches)) {
            throw new \UnexpectedValueException("Unable to process callable string '{$callable}'");
        }

        $instance = app($matches['class']);

        $result = $instance->{$matches['method']}($this);

        return $result === null ? true : (bool) $result;
    }


    // ------------------------------------------------------------------------------
    //      Configuration
    // ------------------------------------------------------------------------------

    /**
     * Takes the set config and creates a normalized version.
     *
     * This can also take stapler configs and normalize them for paperclip.
     *
     * @return array
     */
    protected function normalizeConfig()
    {
        $config = $this->config;

        if ( ! array_has($config, 'variants') && array_has($config, 'styles')) {
            $config['variants'] = array_get($config, 'styles', []);
        }
        array_forget($config, 'styles');

        if ( ! array_has($config, 'variants')) {
            $config['variants'] = config('paperclip.variants.default');
        }

        // Normalize variant definitions
        foreach (array_get($config, 'variants', []) as $variant => $options) {
            // Assume dimensions if not an array
            if ( ! is_array($options)) {
                $options = ['resize' => ['dimensions' => $options]];
                array_set($config, "variants.{$variant}", $options);
            }

            if (array_key_exists('dimensions', $options)) {
                $options = ['resize' => $options];
                array_set($config, "variants.{$variant}", $options);
            }

            // If auto-orient is set, extract it to its own step
            if (    (   array_get($options, 'resize.auto-orient')
                    ||  array_get($options, 'resize.auto_orient')
                    )
                && ! array_has($options, 'auto-orient')
            ) {
                $options = array_merge(['auto-orient' => []], $options);
                array_set($config, "variants.{$variant}", $options);
                array_forget($config, [
                    "variants.{$variant}.resize.auto-orient",
                    "variants.{$variant}.resize.auto_orient",
                ]);
            }
        }

        // Simple renames of stapler config keys
        $renames = [
            'url'            => 'path',
            'keep_old_files' => 'keep-old-files',
            'preserve_files' => 'preserve-files',
        ];

        foreach ($renames as $old => $new) {
            if ( ! array_has($config, $old)) {
                continue;
            }

            if ( ! array_has($config, $new)) {
                $config[ $new ] = array_get($config, $old);
            }
            array_forget($config, $old);
        }

        return $config;
    }

    /**
     * @param string      $key
     * @param string|null $default
     * @return mixed
     */
    protected function getConfigValue($key, $default = null)
    {
        if (array_has($this->normalizedConfig, $key)) {
            return array_get($this->normalizedConfig, $key);
        }

        // Fall back to default configured values
        $map = [
            'keep-old-files' => 'keep-old-files',
            'preserve-files' => 'preserve-files',
            'storage'        => 'storage.disk',
            'path'           => 'path.base-path',

            'attributes.size'         => 'model.attributes.size',
            'attributes.content_type' => 'model.attributes.content_type',
            'attributes.updated_at'   => 'model.attributes.updated_at',
            'attributes.created_at'   => 'model.attributes.created_at',
            'attributes.variants'     => 'model.attributes.variants',
        ];

        if ( ! in_array($key, array_keys($map))) {
            return $default;
        }

        return config('paperclip.' . $map[ $key ], $default);
    }

}
