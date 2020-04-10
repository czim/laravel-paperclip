# Configuration

## Stapler Compatibility

For those used to working with [codesleeve/stapler](https://github.com/CodeSleeve/stapler), here is more information on [Stapler compatibility and configuration](STAPLER.md).


## Variants

The main thing to configure for the average attachment, is its `variants`.
Note that this is not required (an `original` version of the attachment is always available).

### Defining an attachment without any variants

A model with the following constructor would have an `'image'` attachment without any variants.

```php
<?php
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('image');
    }
```


### Configuration options

- `variants` (array of arrays)  
Configured variants for the attachment.
- `url` (string)  
The default fallback URL to return when no attachment is stored.
- `urls` (array of strings)  
A list of fallback URLs to return for each variant, when no attachment is stored.
- `extensions` (array of strings)  
A list of extensions, keyed by variant name, for variants that are stored with an extension different from the original file.
- `types` (array of strings)  
A list of mimetypes, keyed by variant name, for variants that are stored with a mimetype different from the original file.
- `keep-old-files` (boolean)  
Whether to not to delete previously attached files before storing a new attachment.
- `preserve-files` (boolean)  
Whether to keep files even after a model is deleted.  
- `before` (string)  
To set a hook to call before a new file is stored.
- `after` (string)  
To set a hook to call after a new file is stored.
- `storage` (string)  
The Laravel storage disk to use. This allows overriding the default configured storage.
- `path` (string)  
The path, with placeholders. Further information below.
- `variant-path` (string)  
The path to use for variants, with placeholders. Just like `path`, but only for variants.

Some of these options can also be set globally in the paperclip config file.
If these values are not set for the attachment, the global values are used. 


### Object configuration

To make for easier configuration, fluent setter objects are available to define variants and variant file-handling steps. This avoids the need to know many details about the array syntax, and offers auto-completion in your IDE.

The `\Czim\Paperclip\Config\Variant` class may be used to define any variant.
The `\Czim\Paperclip\Config\Steps\AutoOrientStep` and `\Czim\Paperclip\Config\Steps\ResizeStep` classes may be used for fluent configuration of file-handling steps. 

Arrays and fluent models may be mixed and used interchangeably.
 
```php
<?php
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Config\Steps\AutoOrientStep;
use Czim\Paperclip\Config\Steps\ResizeStep;

public function __construct(array $attributes = [])
{
    $this->hasAttachedFile('image', [
        'variants'  => [
            'thumb' => ResizeStep::make()->square(100), // = '100x100'
            Variant::make('landscape')->steps([
                AutoOrientStep::make(),
                ResizeStep::make()->width(300), // = '300x'
            ]),
            Variant::make('portrait')->steps([
                AutoOrientStep::make(),
                ResizeStep::make()->height(300), // = 'x300'
            ]),
            'cropped' => [
                AutoOrientStep::make(),
                ResizeStep::make()->width(150)->height(300)->crop(), // = '150x300#'
            ],
            'ignored_aspect_ratio' => [
                AutoOrientStep::make(),
                ResizeStep::make()->square(100)->ignoreRatio(), // = '100x100!'
            ],
            // The Variant class lets you set further properties
            Variant::make('some_variant')
                ->steps([
                    AutoOrientStep::make(),
                    'resize' => '100x100#',   
                ])
                ->url('http://domain.com/default/missing-image.jpg')
                ->extension('jpg'),
        ],
    ]);
    
    // ...
```

Note that using the `Variant` object allows you to set the (expected) extension and fallback URL directly for a specific variant, rather than setting it its relevant separate configuration array (see below for further information on extensions and fallback URLs). 

Advanced use: any `Arrayable` object may be used to define variant steps, provided the array output is compatible. Please refer to the code and tests for further information.


### Array configuration

It is also possible to configure an attachment with just an array (the classic approach):

```php
<?php
public function __construct(array $attributes = [])
{
    $this->hasAttachedFile('image', [
        'variants'  => [
            'thumb' => '100x100',
            'landscape' => [
                'auto-orient' => [],
                'resize'      => [
                    'dimensions' => '300x',
                ]
            ],
        ],
    ]);
```


### Resizing: callable resizer and using imagine

It is also possible to configure an attachment with just an array and using imagine for custom resizing:

```php
<?php

public function resizeHandle($width, $height)
{
    return function ($file, $imagine) use ($width, $height) {   
        // ...
    }
}

public function __construct(array $attributes = [])
{
    $this->hasAttachedFile('image', [
        'variants'  => [
            'thumb' => '100x100',
            'large' => [
                'auto-orient' => [],
                'resize'      => [
                    'dimensions' => $this->resizeHandle(800, 600),
                ]
            ],
        ],
    ]);
}
```


### Fallback URLs for missing attachments

When no image is stored for a given attachment, any `url()` calls will return `null`.
It is possible to configure a fallback URL to return instead:

```php
<?php
    $this->hasAttachedFile('image', [
        'variants'  => [ 
            'thumb' => '100x100',
        ],
        // This URL is given for attachments with no stored file, for 'original',
        // and any variants that have no specific variant fallback URL set.
        'url'  => 'http://domain.com/missing_image.jpg',
        'urls' => [
            // This fallback URL is only given for the 'thumb' variant.
            'thumb' => 'http://domain.com/missing_thumbnail_image.jpg',
        ],
    ]);
```


### Indicating variant extension

Usually variants will keep the original file extension. In some cases, however, you may want to convert files or derive files from originals with a different extension.

In those cases, Paperclip will need to be able to match extensions to variants in order to generate the correct URLs and paths. This can be done in two ways:

- The `extensions` key in the attachment configuration.  
    If specific variants has an alternative extension, this may be indicated as follows. In this example, two variants will keep the same extension, but two custom variants would have a different extension. 
    
```php
<?php
[
    'variants'  => [
        'medium' => '300x300', 
        'thumb' => '100x100',
        'special' => [
            'derive-description-text' => [],    
        ],
        'converted' => [
            'convert-to-bmp' => [],    
        ],
    ],
    'extensions' => [
        'special'   => 'txt',
        'converted' => 'bmp',
    ],
];
```
 
- The `variants` attribute on the parent model of the attachment.  
    The configuration `extensions` approach above will require manually indicating the extensions per variant. 
    This may be automated by enabling the `variants` attribute on the parent model. 
    This is a text column with JSON-encoded information on actually processed variants, which will include the extension for the variant.
    Note that if a variant strategy may result in files with different extensions, this is the only way to allow Paperclip to reliably generate URLs to that variant.   

Note that when using the fluent object variant configuration, you may also set the extension on the variant object directly:

```php
    // ..
    [
        new Variant::make('thumbnail')
            ->steps([ Resize::make()->square(64), /* ... */ ])
            ->extension('png'),
    ]
    // ..
```


### Before and After Processing Hooks

To hook into the process of uploading paperclip attached files, set the `before` and/or `after` configuration keys for the attachment. 
This may be a `callable` anonymous function (not recommended for models that should be serializable!) or a string with a `ClassFQN@methodName` format.

Examples:

```php
<?php
public function __construct(array $attributes = [])
{
    $this->hasAttachedFile('image', [
        'before' => function ($attachment) { /* Do something here */ },
        'after'  => 'YourHook\HelperClass@yourMethodName',
    ]);
    
    // ...
```

The hook method that is called should expect one parameter, which is the current `Czim\Paperclip\Attachment\Attachment` instance being processed (type-hintable interface: `Czim\Paperclip\Attachment\AttachmentInterface`).


### Resize dimension syntax

When using the array syntax to define resize `'dimensions'`, this takes the same syntax as Stapler did (Examples: `300x300`, `640x480!`, `x40`).

[Refer this documentation](https://github.com/CodeSleeve/stapler/blob/master/docs/imageprocessing.md) for compatible examples.
