
# Laravel Paperclip: File Attachment Solution

Allows you to attach files to Eloquent models.

This is a re-take on [CodeSleeve's Stapler](https://github.com/CodeSleeve/stapler). It is mainly intended to be more reusable and easier to adapt to different Laravel versions. Despite the name, this should not be considered a match for Ruby's Paperclip gem.

Instead of tackling file storage itself, it uses Laravel's internal storage drivers and configuration.

This uses [czim/file-handling](https://github.com/czim/file-handling) under the hood, and any of its (and your custom written) variant manipulations may be used with this package.


## Installation

Via Composer:

``` bash
$ composer require czim/laravel-paperclip
```

Add the service provider to the `app.php` config file:

``` php
Czim\Paperclip\PaperclipServiceProvider::class,
```

Publish the configuration file:

``` bash
php artisan vendor:publish
```


## Usage

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

If it is added and configured to be used (more on that [in the config section](CONFIG.md)), JSON information about variants will be stored in it.  


### Attachment Configuration

To add an attachment to a model:

- Make it implement `Czim\Paperclip\Contracts\AttachableInterface`.
- Make it use the `Czim\Paperclip\Model\PaperclipTrait`.
- Configure attachments in the constructor (very similar to Stapler)

```php
<?php
class Comment extends Model implements Czim\Paperclip\Contracts\AttachableInterface
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


### Variant Configuration

For the most part, the configuration of variants ('styles') is nearly identical to Stapler, so it should be easy to make the transition either way. 

[Get more information on configuration here](CONFIG.md).


#### Custom Variants

The file handler comes with a few common variant strategies, including resizing images and taking screenshots from videos.
It is easy, however, to add your own custom strategies to manipulate files in any way required.

Variant processing is handled by [the file-handler package](https://github.com/czim/file-handling).
Check out its source to get started writing custom variant strategies.


### Storage configuration

You can configure a storage location for uploaded files by setting up a Laravel storage (in `config/filesystems.php`), and registering it in the `config/paperclip.php` config file.

Make sure that `paperclip.storage.base-urls.<your storage disk>` is set, so valid URLs to stored content are returned.


## Differences with Stapler

One major difference is that the `convert_options` configuration settings are no longer available. Conversion options are now handled at the level of the variant strategies.

You can set them per attachment configuration, or modify the variant strategy to use a custom global configuration.

Another difference is that this package does not handle (s3) storage.
All storage is performed through Laravel's storage solution.

A final change is that the trait uses its own boot method, not the global Model's `boot()`, making this package less likely to conflict with other traits and model implementations.


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
