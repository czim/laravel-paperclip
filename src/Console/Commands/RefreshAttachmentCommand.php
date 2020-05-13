<?php

namespace Czim\Paperclip\Console\Commands;

use Czim\Paperclip\Contracts\AttachableInterface;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Exceptions\ReprocessingFailureException;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use UnexpectedValueException;

class RefreshAttachmentCommand extends Command
{
    const DEFAULT_CHUNK_SIZE = 500;

    protected $signature = 'paperclip:refresh
        { class : The model class to refresh attachments on }
        { --attachments= : A list of specific attachments to refresh, comma-separated }
        { --variants= : A list of specific variants to refresh, comma-separated }
        { --dont-order= : If set, disabled ascending sort on the model\'s key column }
        { --start= : Optional ID to start processing at }
        { --stop= : Optional ID to stop processing after }';

    protected $description = "Regenerate variants for a given model's attachments";


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = $this->getAttachableModelInstanceForClass($this->argument('class'));

        $attachmentKeys = $this->getAttachmentsToProcess($model);

        if (empty($attachmentKeys)) {
            $this->warn('No attachments selected or available for this model, nothing to process.');
            return;
        }

        $query = $this->getModelInstanceQuery($model);
        $count = $query->count();

        $this->progressStart($count);

        foreach ($this->generateModelInstances($query, $count) as $instances) {
            /** @var \Illuminate\Support\Collection|Model[]|AttachableInterface[] $instances */
            foreach ($instances as $instance) {

                foreach ($instance->getAttachedFiles() as $attachmentKey => $attachment) {

                    if ( ! in_array($attachmentKey, $attachmentKeys)) {
                        continue;
                    }

                    $this->processAttachmentOnModelInstance($instance, $attachment);
                }

                $this->progressAdvance();
            }
        }

        $this->progressFinish();

        $this->info('Done.');
    }

    /**
     * @param Model               $model
     * @param AttachmentInterface $attachment
     * @throws ReprocessingFailureException
     */
    protected function processAttachmentOnModelInstance(Model $model, AttachmentInterface $attachment)
    {
        $specificVariants   = $this->getVariantsToProcess();
        $matchedVariants    = array_intersect($specificVariants, $attachment->variants());
        $processAllVariants = in_array('*', $specificVariants);


        if ( ! $processAllVariants && ! count($matchedVariants)) {

            throw new UnexpectedValueException(
                "Attachment '{$attachment->name()}' on " .  get_class($model)
                . ' does not have any of the indicated variants '
                . '(' . implode(', ', $specificVariants) . ')'
            );
        }


        try {
            if ($processAllVariants) {
                $attachment->reprocess();
            } else {
                $attachment->reprocess($specificVariants);
            }

        } catch (Exception $e) {

            throw new ReprocessingFailureException(
                "Failed to reprocess attachment '{$attachment->name()}' of "
                . get_class($model) . " #{$model->getKey()}: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param string $class
     * @return Model|AttachableInterface
     */
    protected function getAttachableModelInstanceForClass($class)
    {
        if ( ! is_a($class, AttachableInterface::class, true)) {
            throw new RuntimeException("'{$class}' is not a valid attachable model class.");
        }

        return app($class);
    }

    /**
     * Returns the attachment names that should be procesed for a given model.
     *
     * This takes into account the given attachments listed as a command option,
     * and filters out any attachments that don't exist.
     *
     * @param AttachableInterface $model
     * @return string[]
     */
    protected function getAttachmentsToProcess(AttachableInterface $model)
    {
        $attachments     = $model->getAttachedFiles();
        $attachmentNames = $this->option('attachments');

        $availableAttachmentNames = array_map(
            function (AttachmentInterface $attachment) {
                return $attachment->name();
            },
            $attachments
        );

        if (empty($attachmentNames)) {
            return $availableAttachmentNames;
        }

        $attachmentNames = explode(',', str_replace(', ', ',', $attachmentNames));

        $unavailableNames = array_diff($attachmentNames, $availableAttachmentNames);
        if (count($unavailableNames)) {
            throw new UnexpectedValueException(
                get_class($model) . ' does not have attachment(s): ' . implode(', ', $unavailableNames)
            );
        }

        return array_intersect($availableAttachmentNames, $attachmentNames);
    }

    /**
     * @return string[]
     */
    protected function getVariantsToProcess()
    {
        $variants = $this->option('variants');

        if ( ! $variants) {
            return ['*'];
        }

        return explode(',', str_replace(', ', ',', $variants));
    }

    /**
     * Returns base query for returning all model instances.
     *
     * @param Model $model
     * @return Builder
     */
    protected function getModelInstanceQuery(Model $model)
    {
        $query = $model->query();

        $this->applyOrderingToModelInstanceQuery($query);
        $this->applyStartAndStopLimitsToQuery($query);

        return $query;
    }

    /**
     * Sets up and returns generator for model instances.
     *
     * This also starts the progress bar, given the total count of matched
     *
     * @param Builder $query
     * @param int     $totalCount
     * @return \Generator
     */
    protected function generateModelInstances($query, $totalCount)
    {
        $chunkSize = $this->getChunkSize();

        $chunkCount = ceil($totalCount / $chunkSize);

        for ($x = 0; $x < $chunkCount; $x++) {

            $skip = $x * $chunkSize;

            yield $query
                ->skip($skip)
                ->take($chunkSize)
                ->get();
        }
    }

    /**
     * @param Builder $query
     */
    protected function applyOrderingToModelInstanceQuery($query)
    {
        if ($this->option('dont-order')) {
            return;
        }

        $query->orderBy('id');
    }

    /**
     * @param Builder $query
     */
    protected function applyStartAndStopLimitsToQuery($query)
    {
        $startAt   = $this->option('start');
        $stopAfter = $this->option('stop');

        if ( ! $startAt && ! $startAt) {
            return;
        }

        $model = $query->getModel();

        if ($startAt) {
            $query->where($model->getKeyName(), '>=', $startAt);
        }

        if ($stopAfter) {
            $query->where($model->getKeyName(), '<=', $stopAfter);
        }
    }

    /**
     * @return int
     */
    protected function getChunkSize()
    {
        return config('paperclip.processing.chunk-size', static::DEFAULT_CHUNK_SIZE);
    }

    /**
     * @param int $count
     */
    protected function progressStart($count)
    {
        $this->output->progressStart($count);
    }

    protected function progressAdvance()
    {
        $this->output->progressAdvance();
    }

    protected function progressFinish()
    {
        $this->output->progressFinish();
    }
}
