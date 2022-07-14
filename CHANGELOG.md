# Changelog

## Laravel 8 and up

### [4.0.1] - 2022-07-14

Fixed return value for setAttribute not matching Eloquent internals (wimski).

### [4.0.0] - 2021-11-30

Added PHP 8.0 and 8.1 support (thanks Miljoen!).

## Laravel 7

### [3.2.2] - 2020-12-19

Fixed an issue with variant step configuration where the order of variant steps was reversed unintentionally.
This caused issues with auto-orient & resize steps in combination.

### [3.2.1] - 2020-06-18

Attachment setters now call `clearTarget()` to reset the interpolator.
This is highly unlikely to make a difference for your setup.
This may help you if you are attempting to clone data, f.i. between models, at the Attachment object level.

### [3.2.0] - 2020-05-13

Potentially breaking changes here!

Now depends on file-handling 2. All interfaces in that package have been updated to make use of PHP 7.1+ features.
If you have custom variant strategies or extend these classes, update their method signatures.
This should be easy and take no more than a few minutes.

### [3.1.0] - 2020-04-05

Fixed issues with attribute handling, replaced attribute hack with nicer model-event based approach (Thanks to Austen Cameron).

### [3.0.1] - 2020-03-19

Support for Laravel 7, not backwards compatible.

## Laravel 5.8 and 6

### [2.7.5] - 2020-06-18

See changes for 3.2.1.

### [2.7.4] - 2020-03-05

Now fires an event (`AttachmentSavedEvent`) on saving an attachment.

### [2.7.3] - 2019-12-27

Now fires events rather than throws exceptions on processing errors.
Added `paperclip.processing.errors.events` boolean configuration toggle for this.
This allows mass reprocessing to skip attachments with errors.

### [2.7.2] - 2019-07-21

Artisan `paperclip:refresh` command now has `--variants=` option to refresh only specific variants.

### [2.7.1] - 2019-07-19

Temporary files are now deleted after variants are processed.

### [2.7.0] - 2019-03-01

Updated to support Laravel 5.8.
No functional changes.


## Laravel 5.5 and up

### [2.6.3] - 2019-07-21

See changes for 2.7.2.

### [2.6.2] - 2019-07-19

See changes for 2.7.1.

### [2.6.1] - 2019-01-07

Minor cloud storage fix.

### [2.6.0] - 2018-11-04

Rewrote configuration handling. This used to be all-array, now it is object-based.

This includes a breaking change for basic usage: Stapler configuration support is not enabled by default.
To enable it, set the `paperclip.config.mode` configuration option to `'stapler'`.

It may also break custom extensions of paperclip classes. Note especially the interface change for the `Attachment` class: `setConfig()` now takes a `ConfigInterface` object, instead of an array.
This is unlikely to affect the average application of this package.

Additionally, support has been added for setting a default URL that is returned when no file is stored.
This must be set per attachment, and may set per variant.

Further, configuration options for default variants has improved.
It is now possible to always merge in the default variants into any and all specific attachment variants,
using the `paperclip.variants.merge-default` setting.

The [CONFIG](CONFIG.md) readme has been updated to reflect these changes.


### [2.5.7] - 2018-10-29

Added possibility to configure variants using fluent syntax configuration objects.
This is entirely optional. The documentation has been updated to reflect this.


### [2.5.6] - 2018-10-25

Improved configuration defaults and analysis for storage and base URL.
If no storage driver is configured, or if it is incorrectly configured, a descriptive exception is thrown.
If no base-URL could be determined, an exception is thrown.

It is no longer required to set `paperclip.storage.base-urls` entries if the relevant `filesystems.disks.<disk>.url` is set.

### [2.5.5] - 2018-10-14

Now allows configuration of deletion hash through `paperclip.delete-hash` key.

### [2.5.4] - 2018-09-27

Fixed issue with serialization of Attachment instance (and, by extension, any models with the paperclip trait).
This *may* include a breaking change, but only if you changed service provision or modified this package's instantiation or factory logic.

### [2.5.3] - 2018-09-08

Fixed issue with Laravel `5.6.37` and up (with a hack).
Fixed a broken config reference to filesystem disk 'local'-checking.

### [2.5.2] - 2018-06-28

Fixed issue where variant URLs and storage paths made use of the original name, rather than the variant's name (and extension).

### [2.5.1] - 2018-05-13

Now actually uses `path.interpolator` configuration setting.


### [2.5.0] - 2018-05-13

There are quite a few breaking changes here!
Please take care when updating, this will likely affect any project relying on this package.

This now relies on version `^1.0` for [czim/file-handling](https://github.com/czim/file-handling), which has many breaking changes of its own. Please consider [its changelog](https://github.com/czim/file-handling/blob/master/CHANGELOG.md) too.


- Removed deprecated `isFilled()` method from `Attachment` (was replaced by `exists()`).
- Configuration changes:
    - Old configuration files will break! Please update accordingly or re-publish when upgrading.
    - The `path.base-path` key is now replaced by `path.original`. This should now include placeholders to make a *full file path including the filename*, as opposed to only a directory. Note that this makes the path interpolation logic more in line with the Stapler handled it.
    - A `path.variant` key is added, where a path can be defined similar to `path.original`. If this is set, it may be used to make an alternative structure for your variant paths (as opposed to the original file).
    - It is recommended to use `:variant` and `:filename` in your placeholdered path for the `path.original` (and `path.variant`) value. See [the config](https://github.com/czim/laravel-paperclip/blob/97d02c77ce724f3e47acb0e17ad3e54e17aa5f12/config/paperclip.php#L65) for an example.
    - `:url` is no longer a usable path interpolation placeholder.
- Attachment changes:
    - The `AttachmentInterface` has been segregated into main and data interfaces (`AttachmentDataInterface`).
    - A historical set of attachment data is provided to the interpolator when handling queued deletes.
        This more accurately reflect the values used to create the file that is to be deleted.
    - Deletion queueing is now done using a `Target` instance and variant names, and uses fixed historical state saving to fix a number of (potential) issues.

- Interpolator changes:
    - The path interpolator now depends on `AttachmentDataInterface` (and its added `getInstanceKey()` method). This is only likely to cause issues if you have extended or implemented your own interpolator.
    - The `url()` method and placeholder have been removed and will no longer be interpolated.
        This was done to prevent the risk of endless recursion.
        (It made no sense to me, anyway: an correct url is the *result* of the interpolation; how could it sensibly be used to interpolate its own result?).
         If you do use this placeholder, please submit an issue with details on how and why, so we can think of a safe solution.


## Laravel 5.4 and below

### [2.1.0] - 2019-02-11

Updates added for 2.6.1.

### [2.0.1] - 2018-06-28

See 2.5.2.

### [2.0.0] - 2018-05-13

This merges the changes for 2.5.0 and 2.5.1 in a new major version for Laravel 5.4 and earlier.

[4.0.1]: https://github.com/czim/laravel-paperclip/compare/4.0.0...4.0.1
[4.0.0]: https://github.com/czim/laravel-paperclip/compare/3.2.1...4.0.0

[3.2.1]: https://github.com/czim/laravel-paperclip/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/czim/laravel-paperclip/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/czim/laravel-paperclip/compare/3.0.1...3.1.0
[3.0.1]: https://github.com/czim/laravel-paperclip/compare/2.7.4...3.0.1

[2.7.5]: https://github.com/czim/laravel-paperclip/compare/2.7.4...2.7.5
[2.7.4]: https://github.com/czim/laravel-paperclip/compare/2.7.3...2.7.4
[2.7.3]: https://github.com/czim/laravel-paperclip/compare/2.7.2...2.7.3
[2.7.2]: https://github.com/czim/laravel-paperclip/compare/2.7.1...2.7.2
[2.7.1]: https://github.com/czim/laravel-paperclip/compare/2.7.0...2.7.1
[2.7.0]: https://github.com/czim/laravel-paperclip/compare/2.6.1...2.7.0

[2.6.3]: https://github.com/czim/laravel-paperclip/compare/2.6.2...2.6.3
[2.6.2]: https://github.com/czim/laravel-paperclip/compare/2.6.1...2.6.2
[2.6.1]: https://github.com/czim/laravel-paperclip/compare/2.6.0...2.6.1
[2.6.0]: https://github.com/czim/laravel-paperclip/compare/2.5.7...2.6.0

[2.5.7]: https://github.com/czim/laravel-paperclip/compare/2.5.6...2.5.7
[2.5.6]: https://github.com/czim/laravel-paperclip/compare/2.5.5...2.5.6
[2.5.5]: https://github.com/czim/laravel-paperclip/compare/2.5.4...2.5.5
[2.5.4]: https://github.com/czim/laravel-paperclip/compare/2.5.3...2.5.4
[2.5.3]: https://github.com/czim/laravel-paperclip/compare/2.5.2...2.5.3
[2.5.2]: https://github.com/czim/laravel-paperclip/compare/2.5.1...2.5.2
[2.5.1]: https://github.com/czim/laravel-paperclip/compare/2.5.0...2.5.1
[2.5.0]: https://github.com/czim/laravel-paperclip/compare/1.5.2...2.5.0

[2.1.0]: https://github.com/czim/laravel-paperclip/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/czim/laravel-paperclip/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/czim/laravel-paperclip/compare/1.0.3...2.0.0
