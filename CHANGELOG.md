# Changelog

## 1.*

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
                                                                                   

[2.5.0]: https://github.com/czim/laravel-paperclip/compare/1.5.2...2.5.0
