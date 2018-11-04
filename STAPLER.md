# Stapler compatibility

This package works very much like [CodeSleeve's Stapler](https://github.com/CodeSleeve/stapler) did.
It is even possible to allow (almost) exactly the same configuration arrays to be used with Paperclip as they worked in Stapler.

To enable this functionality, set the `paperclip.config.mode` to `'stapler'`.
 
 For example, if so configured, the following configuration works as expected:

```php
<?php
public function __construct(array $attributes = [])
{
    $this->hasAttachedFile('image', [
        'styles'  => [
            'medium' => [
                'dimensions'  => '300x300', 
                'auto_orient' => true,
            ],
            'thumb' => '100x100',
        ],
    ]);
    
    // ...
```

This will be internally normalized to auto-orient a medium-sized image, and resize a thumb-sized image (without orienting).

This stapler configuration is functionally identical to this paperclip configuration:

```php
<?php
use \Czim\Paperclip\Config\Variant;
use \Czim\Paperclip\Config\Steps\AutoOrientStep;
use \Czim\Paperclip\Config\Steps\ResizeStep;

// ...

    $this->hasAttachedFile('image', [
        'variants'  => [
            Variant::make('medium')
                ->steps([
                    AutoOrientStep::make(),
                    ResizeStep::make()
                        ->square(300),    
                ]),
            Variant::make('thumb')->steps(
                ResizeStep::make()->square(100)
            )
        ],
    ]);
```


## Defining the default fallback URL for when no attachment is et.

In Stapler, the `'url'` array key refers to what is `'path'` does in Paperclip.

To set the default fallback URL (used for the `original` version of the attachment, or any variant that has no specific fallback URL set), use the `'missing_url'` array key:

```php
<?php
    $this->hasAttachedFile('image', [
        'styles'  => [
            'thumb' => '100x100',
        ],
        'missing_url' => 'http://domain.com/missing_image.jpg',
        'urls' => [
            'thumb' => 'http://domain.com/missing_image_thumb.jpg',  
        ],
    ]);
```

Note that the global default for a missing image can not be set (as it could in Stapler); it was not possible in Stapler to set variant-specific fallback URLs, so the `'urls'` key has no Stapler-equivalent.
