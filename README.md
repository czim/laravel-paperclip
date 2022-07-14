[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.com/czim/laravel-paperclip.svg?branch=master)](https://travis-ci.com/czim/laravel-paperclip)
[![Coverage Status](https://coveralls.io/repos/github/czim/laravel-paperclip/badge.svg?branch=master)](https://coveralls.io/github/czim/laravel-paperclip?branch=master)

# Laravel Paperclip: File Attachment Solution

Allows you to attach files to Eloquent models.

This is a re-take on [CodeSleeve's Stapler](https://github.com/CodeSleeve/stapler). It is mainly intended to be more reusable and easier to adapt to different Laravel versions. Despite the name, this should not be considered a match for Ruby's Paperclip gem.

Instead of tackling file storage itself, it uses Laravel's internal storage drivers and configuration.

This uses [czim/file-handling](https://github.com/czim/file-handling) under the hood, and any of its (and your custom written) variant manipulations may be used with this package.


## Version Compatibility

| Laravel       | Package  | PHP Version   |
|:--------------|:---------|:--------------|
| 5.4 and below | 1.0, 2.1 | 7.4 and below |
| 5.5           | 1.5, 2.5 | 7.4 and below |
| 5.6, 5.7      | 2.6      | 7.4 and below |
| 5.8, 6        | 2.7      | 7.4 and below |
| 7, 8          | 3.2      | 7.4 and below |
| 7, 8, 9       | 4.0      | 8.0 and up    |

## Change log

[View the changelog](CHANGELOG.md).


## Installation

Via Composer:

``` bash
$ composer require czim/laravel-paperclip
```

Autodiscover may be used to register the service provider automatically.
Otherwise, you can manually register the service provider in `config/app.php`:

```php
<?php
   'providers' => [
        ...
        Czim\Paperclip\Providers\PaperclipServiceProvider::class,
        ...
   ],
```

Publish the configuration file:

``` bash
php artisan vendor:publish --provider="Czim\Paperclip\Providers\PaperclipServiceProvider"
```


## Set up and Configuration

### Model Preparation

Modify the database to add some columns for the model that will get an attachment. Use the attachment key name as a prefix.

An example migration:

```php
<?php
    Schema::create('your_models_table', function (Blueprint $table) {
        $table->string('attachmentname_file_name')->nullable();
        $table->integer('attachmentname_file_size')->nullable();
        $table->string('attachmentname_content_type')->nullable();
        $table->timestamp('attachmentname_updated_at')->nullable();
    });
```

Replace `attachmentname` here with the name of the attachment.
These attributes should be familiar if you've used Stapler before.

A `<key>_variants` text or varchar column is optional:

```php
<?php
    $table->string('attachmentname_variants', 255)->nullable();
```

A `text()` column is recommended in cases where a seriously *huge* amount of variants are created.

If it is added and configured to be used (more on that [in the config section](CONFIG.md)), JSON information about variants will be stored in it.


### Attachment Configuration

To add an attachment to a model:

- Make it implement `Czim\Paperclip\Contracts\AttachableInterface`.
- Make it use the `Czim\Paperclip\Model\PaperclipTrait`.
- Configure attachments in the constructor (very similar to Stapler)

```php
<?php
class Comment extends Model implements \Czim\Paperclip\Contracts\AttachableInterface
{
    use \Czim\Paperclip\Model\PaperclipTrait;

    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('image', [
            'variants' => [
                'medium' => [
                    'auto-orient' => [],
                    'resize'      => ['dimensions' => '300x300'],
                ],
                'thumb' => '100x100',
            ],
            'attributes' => [
                'variants' => true,
            ],
        ]);

        parent::__construct($attributes);
    }
}
```

Note: If you perform the `hasAttachedFile()` call(s) *after* the `parent::__construct()` call,
everything will work the same, except that you cannot assign an image directly when creating a model.
`ModelClass::create(['attachment' => ...])` will not work in that case.


Since version `2.5.7` it is also possible to use an easier to use fluent object syntax for defining variant steps:

```php
<?php
    use \Czim\Paperclip\Config\Variant;
    use \Czim\Paperclip\Config\Steps\AutoOrientStep;
    use \Czim\Paperclip\Config\Steps\ResizeStep;

    // ...

    $this->hasAttachedFile('image', [
        'variants' => [
            Variant::make('medium')->steps([
                AutoOrientStep::make(),
                ResizeStep::make()->width(300)->height(150)->crop(),
            ]),
            Variant::make('medium')->steps(ResizeStep::make()->square(100)),
        ],
    ]);
```


### Variant Configuration

For the most part, the configuration of variants is nearly identical to Stapler, so it should be easy to make the transition either way.

Since version `2.6`, Stapler configuration support is disabled by default, but legacy support for this may be enabled by setting the `paperclip.config.mode` to `'stapler'`.

[Get more information on configuration here](CONFIG.md).


#### Custom Variants

The file handler comes with a few common variant strategies, including resizing images and taking screenshots from videos.
It is easy, however, to add your own custom strategies to manipulate files in any way required.

Variant processing is handled by [the file-handler package](https://github.com/czim/file-handling).
Check out its source to get started writing custom variant strategies.


### Storage configuration

You can configure a storage location for uploaded files by setting up a Laravel storage (in `config/filesystems.php`), and registering it in the `config/paperclip.php` config file.

Make sure that `paperclip.storage.base-urls.<your storage disk>` is set, so valid URLs to stored content are returned.

### Hooks Before and After Processing

It is possible to 'hook' into the paperclip goings on when files are processed. This may be done by using the `before` and/or `after` configuration keys. Before hooks are called after the file is uploaded and stored locally, but before variants are processed; after hooks are called when all variants have been processed.

More information and examples are in [the Config section](CONFIG.md).

### Events

The following events are available:

* `AttachmentSavedEvent`: dispatched when any attachment is saved with a file

### Refreshing models

When changing variant configurations for models, you may reprocess variants from previously created attachments with the `paperclip:refresh` Artisan command.

Example:

```bash
php artisan paperclip:refresh "App\Models\BlogPost" --attachments header,background
```


## Usage

Once a model is set up and configured for an attachment, you can simply set the attachment attribute on that model to create an attachment.

```php
<?php
public function someControllerAction(Request $request) {

    $model = ModelWithAttachment::first();

    // You can set any UploadedFile instance from a request on
    // the attribute you configured a Paperclipped model for.
    $model->attachmentname = $request->file('uploaded');

    // Saving the model will then process and store the attachment.
    $model->save();

    // ...
}
```


### Setting attachments without uploads

Usually, you will want to set an uploaded file as an attachment. If you want to store a file from within your application, without the context of a request or a file upload, you can use the following approach:

```php
<?php
// You can use the built in SplFileInfo class:
$model->attachmentname = new \SplFileInfo('local/path/to.file');


// Or a file-handler class that allows you to override values:
$file = new \Czim\FileHandling\Storage\File\SplFileInfoStorableFile();
$file->setData(new \SplFileInfo('local/path/to.file'));
// Optional, will be derived from the file normally
$file->setMimeType('image/jpeg');
// Optional, the file's current name will be used normally
$file->setName('original-file-name.jpg');
$model->attachmentname = $file;


// Or even a class representing raw content
$raw = new \Czim\FileHandling\Storage\File\RawStorableFile();
$raw->setData('... string with raw content of file ...');
$raw->setMimeType('image/jpeg');
$raw->setName('original-file-name.jpg');
$model->attachmentname = $raw;
```


### Clearing attachments

In order to prevent accidental deletion, setting the attachment to `null` will *not* destroy a previously stored attachment.
Instead you have to explicitly destroy it.

```php
<?php
// You can set a special string value, the deletion hash, like so:
$model->attachmentname = \Czim\Paperclip\Attachment\Attachment::NULL_ATTACHMENT;
// In version 2.5.5 and up, this value is configurable and available in the config:
$model->attachmentname = config('paperclip.delete-hash');

// You can also directly clear the attachment by flagging it for deletion:
$model->attachmentname->setToBeDeleted();


// After any of these approaches, saving the model will make the deletion take effect.
$model->save();
```


## Differences with Stapler

- Paperclip does not handle (s3) storage internally, as Stapler did.
All storage is performed through Laravel's storage solution.
You can still use S3 (or any other storage disk), but you will have to configure it in Laravel's storage configuration first.
It is possible to use different storage disks for different attachments.

- Paperclip *might* show slightly different behavior when storing a `string` value on the attachment attribute. It will attempt to interpret the string as a URI (or a dataURI), and otherwise treat the string as raw text file content.

If you wish to force storing the contents of a URL without letting Paperclip interpret it, you have some options. You can use the `Czim\FileHandling\Storage\File\StorableFileFactory@makeFromUrl` method and its return value.
Or, you can download the contents yourself and store them in a `Czim\FileHandling\Storage\File\RawStorableFile` (e.g.: `(new RawStorableFile)->setData(file_get_contents('your-URL-here'))`). You can also download the file to local disk, and store it on the model through an `\SplFileInfo` instance (see examples on the main readme page).

- The `convert_options` configuration settings are no longer available.
Conversion options are now handled at the level of the variant strategies.
You can set them per attachment configuration, or modify the variant strategy to use a custom global configuration.

- The refresh command (`php artisan paperclip:refresh`) is very similar to stapler's refresh command, but it can optionally take a `--start #` and/or `--stop #` option, with ID numbers.
This makes it possible to refresh only a subset of models.
Under the hood, the refresh command is also much less likely to run out of memory (it uses a generator to process models in chunks).

- The Paperclip trait uses its own Eloquent boot method, not the global Model's `boot()`.
 This makes Paperclip less likely to conflict with other traits and model implementations.


## Amazon S3 cache-control

If you use Amazon S3 as storage disk for your attachments, note that you can set `Cache-Control` headers in the options for the `filesystems.disks.s3` configuration key.
For example, to set `max-age` headers on all uploaded files to S3, edit `config/filesystems.php` like so:

```
's3' => [
    'driver' => env('S3_DRIVER', 's3'),
    'key'    => env('S3_KEY', 'your-key'),
    'secret' => env('S3_SECRET', 'your-secret'),
    'region' => env('S3_REGION', 'your-region'),
    'bucket' => env('S3_BUCKET', 'your-bucket'),
    'visibility' => 'public',
    'options' => [
        'CacheControl' => 'max-age=315360000, no-transform, public',
    ],
],
```

## Upgrade Guide

### Upgrading from 1.5.* to 2.5.*

**Estimated Upgrade Time: 5 - 10 Minutes**

### Updating Dependencies

Update your `czim/laravel-paperclip` dependency to `^2.5` in your `composer.json` file.

```
	"require": {
		...
		"czim/laravel-paperclip": "^2.5",
		...
	}
```

Then, in your terminal run:

```
composer update czim/laravel-paperclip --with-dependencies
```

In addition, if you are using the `czim/file-handling` package directly, you should upgrade the package to its `^1,0` release, but be sure to checkout the [CHANGELOG](https://github.com/czim/file-handling/blob/master/CHANGELOG.md)

```
	"require": {
		...
		"czim/file-handling": "^1.0",
		...
	}
```

### Update Configuration

Update your `config/paperclip.php` file and replace:

```
        // The base path that the interpolator should use
        'base-path' => ':class/:id_partition/:attribute',
```

With:

```
        // The path to the original file to be interpolated. This will also\
        // be used for variant paths if the variant key is unset.
        'original' => ':class/:id_partition/:attribute/:variant/:filename',

        // If the structure for variant filenames should differ from the
        // original, it may be defined here.
        'variant'  => null,
```

This should now include placeholders to make a full file path including the filename, as opposed to only a directory. Note that this makes the path interpolation logic more in line with the way Stapler handled it.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/czim/laravel-paperclip.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/laravel-paperclip.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/laravel-paperclip
[link-downloads]: https://packagist.org/packages/czim/laravel-paperclip
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
