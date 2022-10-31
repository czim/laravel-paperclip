<?php

declare(strict_types=1);

namespace Czim\Paperclip\Providers;

use Czim\FileHandling\Contracts\Storage\StorableFileFactoryInterface;
use Czim\FileHandling\Contracts\Support\ContentInterpreterInterface;
use Czim\FileHandling\Contracts\Support\MimeTypeHelperInterface;
use Czim\FileHandling\Contracts\Support\UrlDownloaderInterface;
use Czim\FileHandling\Contracts\Variant\VariantProcessorInterface;
use Czim\FileHandling\Contracts\Variant\VariantStrategyFactoryInterface;
use Czim\FileHandling\Storage\File\StorableFileFactory;
use Czim\FileHandling\Support\Container\LaravelContainerDecorator;
use Czim\FileHandling\Support\Content\MimeTypeHelper;
use Czim\FileHandling\Support\Content\UploadedContentInterpreter;
use Czim\FileHandling\Support\Download\UrlDownloader;
use Czim\FileHandling\Variant\VariantProcessor;
use Czim\FileHandling\Variant\VariantStrategyFactory;
use Czim\Paperclip\Attachment\AttachmentFactory;
use Czim\Paperclip\Console\Commands\RefreshAttachmentCommand;
use Czim\Paperclip\Contracts\AttachmentFactoryInterface;
use Czim\Paperclip\Contracts\FileHandlerFactoryInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Czim\Paperclip\Handler\FileHandlerFactory;
use Czim\Paperclip\Path\Interpolator;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Imagine\Gd\Imagine;
use Imagine\Image\ImagineInterface;

class PaperclipServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootConfig();
    }

    public function register(): void
    {
        $this->registerConfig();
        $this->registerCommands();
        $this->registerInterfaceBindings();
    }

    protected function bootConfig(): void
    {
        $this->publishes([
            realpath(dirname(__DIR__) . '/../config/paperclip.php') => config_path('paperclip.php'),
        ]);
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(
            realpath(dirname(__DIR__) . '/../config/paperclip.php'),
            'paperclip'
        );
    }

    protected function registerInterfaceBindings(): void
    {
        $this->registerFileHandlerInterfaceBindings();
        $this->registerVariantStrategyFactory();

        $this->app->singleton(FileHandlerFactoryInterface::class, FileHandlerFactory::class);
        $this->app->singleton(AttachmentFactoryInterface::class, AttachmentFactory::class);
        $this->app->singleton(ImagineInterface::class, $this->getImagineImplementationClass());
    }

    protected function registerFileHandlerInterfaceBindings(): void
    {
        $this->app->singleton(VariantProcessorInterface::class, VariantProcessor::class);
        $this->app->singleton(StorableFileFactoryInterface::class, StorableFileFactory::class);
        $this->app->singleton(MimeTypeHelperInterface::class, MimeTypeHelper::class);
        $this->app->singleton(ContentInterpreterInterface::class, UploadedContentInterpreter::class);
        $this->app->singleton(UrlDownloaderInterface::class, UrlDownloader::class);
        $this->app->singleton(InterpolatorInterface::class, Interpolator::class);
    }

    protected function registerVariantStrategyFactory(): void
    {
        $this->app->singleton(
            VariantStrategyFactoryInterface::class,
            fn (Application $app) => (new VariantStrategyFactory(new LaravelContainerDecorator($app)))
                ->setConfig([
                    'aliases' => config('paperclip.variants.aliases', []),
                ])
        );
    }

    protected function registerCommands(): void
    {
        $this->app->singleton('paperclip.commands.refresh', RefreshAttachmentCommand::class);

        $this->commands([
            'paperclip.commands.refresh',
        ]);
    }

    /**
     * @return class-string<ImagineInterface>
     */
    protected function getImagineImplementationClass(): string
    {
        return config('paperclip.imagine', Imagine::class);
    }
}
