# Migrating From Stapler

Although most functionality from Stapler exists in this package, there are a few
key differences between the two. 

## Differences from Stapler
- One major difference is that the `convert_options` configuration settings are no longer available. Conversion options are now handled at the level of the variant strategies. You can set them per attachment configuration, or modify the variant strategy to use a custom global configuration.

- The Paperclip equivalent for `STAPLER_NULL` is `Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT`.

- Another difference is that this package does not handle (s3) storage. All storage is performed through Laravel's storage drivers and configuration.

- The refresh command (`php artisan paperclip:refresh`) is very similar to stapler's refresh command, but it can optionally take a `--start #` and/or `--stop #` option, with ID numbers. This makes it possible to refresh only a subset of models. _Under the hood, the refresh command is also much less likely to run out of memory (it uses a generator to process models in chunks)._

- A final change is that the trait uses its own boot method, not the global Model's `boot()`, making this package less likely to conflict with other traits and model implementations.

## Migrating from Stapler
Remove the `laravel-stapler` composer dependency and add paperclip via `composer require czim/laravel-paperclip`

Add the service provider to the `config/app.php` file:
``` php
Czim\Paperclip\Providers\PaperclipServiceProvider::class,
```

Publish the configuration file
``` bash
php artisan vendor:publish --provider="Czim\Paperclip\Providers\PaperclipServiceProvider"
```
_Make sure to remove any reference to the Stapler service provider_

Change references of `Codesleeve\Stapler\ORM\EloquentTrait` to `Czim\Paperclip
Model\PaperclipTrait`

Change references of `Codesleeve\Stapler\ORM\StaplerableInterface` to `Czim\Paperclip\Contracts\AttachableInterface`

Change references of `STAPLER_NULL` to use `Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT`