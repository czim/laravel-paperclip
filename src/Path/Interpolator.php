<?php

namespace Czim\Paperclip\Path;

use Czim\FileHandling\Handler\FileHandler;
use Czim\Paperclip\Contracts\AttachmentDataInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Taken from CodeSleeve/Stapler: https://github.com/CodeSleeve/stapler
 * Modified to simplify, hug the interface better and rely on Laravel.
 *
 * @author Travis Bennet
 */
class Interpolator implements InterpolatorInterface
{

    /**
     * Interpolate a string.
     *
     * @param string                  $string
     * @param AttachmentDataInterface $attachment
     * @param string|null             $variant
     * @return string
     */
    public function interpolate($string, AttachmentDataInterface $attachment, $variant = null)
    {
        $variant = $variant ?: '';

        foreach ($this->interpolations() as $key => $value) {

            if (strpos($string, $key) !== false) {
                $string = preg_replace("/$key\b/", $this->$value($attachment, $variant), $string);
            }
        }

        return $string;
    }

    /**
     * Returns a sorted list of all interpolations.
     *
     * @return array
     */
    protected function interpolations()
    {
        return [
            ':app_root'     => 'appRoot',
            ':attribute'    => 'getName',
            ':attachment'   => 'attachment',
            ':basename'     => 'basename',
            ':class_name'   => 'getClassName',
            ':class'        => 'getClass',
            ':extension'    => 'extension',
            ':filename'     => 'filename',
            ':hash'         => 'hash',
            ':id'           => 'id',
            ':id_partition' => 'idPartition',
            ':namespace'    => 'getNamespace',
            ':name'         => 'getName',
            ':secure_hash'  => 'secureHash',
            ':style'        => 'style',
            ':variant'      => 'style',
        ];
    }

    /**
     * Returns the file name.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     *
     * @return string
     */
    protected function filename(AttachmentDataInterface $attachment, $variant = '')
    {
        if ($variant) {
            return $attachment->variantFilename($variant);
        }

        return $attachment->originalFilename();
    }

    /**
     * Returns the application root of the project.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     *
     * @return string
     */
    protected function appRoot(AttachmentDataInterface $attachment, $variant = '')
    {
        return app_path();
    }

    /**
     * Returns the current class name, taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Swingline/Stapler.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function getClass(AttachmentDataInterface $attachment, $variant = '')
    {
        return $this->handleBackslashes($attachment->getInstanceClass());
    }

    /**
     * Returns the current class name, not taking into account namespaces, e.g.
     *
     * @param AttachmentDataInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function getClassName(AttachmentDataInterface $attachment, $variant = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());

        return end($classComponents);
    }

    /**
     * Returns the current class name, exclusively taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Swingline.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     *
     * @return string
     */
    protected function getNamespace(AttachmentDataInterface $attachment, $variant = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());

        return implode('/', array_slice($classComponents, 0, count($classComponents) - 1));
    }

    /**
     * Returns the basename portion of the attached file, e.g 'file' for file.jpg.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function basename(AttachmentDataInterface $attachment, $variant = '')
    {
        return pathinfo($this->filename($attachment, $variant), PATHINFO_FILENAME);
    }

    /**
     * Returns the extension of the attached file, e.g 'jpg' for file.jpg.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function extension(AttachmentDataInterface $attachment, $variant = '')
    {
        return pathinfo($this->filename($attachment, $variant), PATHINFO_EXTENSION);
    }

    /**
     * Returns the id of the current object instance.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function id(AttachmentDataInterface $attachment, $variant = '')
    {
        return $this->ensurePrintable($attachment->getInstanceKey());
    }

    /**
     * Return a secure Bcrypt hash of the attachment's corresponding instance id.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function secureHash(AttachmentDataInterface $attachment, $variant = '')
    {
        return hash(
            'sha256',
            $this->id($attachment, $variant) . $attachment->size() . $this->filename($attachment, $variant)
        );
    }

    /**
     * Return a Bcrypt hash of the attachment's corresponding instance id.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function hash(AttachmentDataInterface $attachment, $variant = '')
    {
        return hash('sha256', $this->id($attachment, $variant));
    }

    /**
     * Generates the id partition of a record, e.g /000/001/234 for an id of 1234.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function idPartition(AttachmentDataInterface $attachment, $variant = '')
    {
        $id = $this->ensurePrintable($attachment->getInstanceKey());

        if (is_numeric($id)) {
            return implode('/', str_split(sprintf('%09d', $id), 3));
        }

        if (is_string($id)) {
            return implode('/', array_slice(str_split($id, 3), 0, 3));
        }

        // @codeCoverageIgnoreStart
        return '';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the pluralized form of the attachment name. e.g.
     * "avatars" for an attachment of :avatar.
     *
     * @param AttachmentDataInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function attachment(AttachmentDataInterface $attachment, $variant = '')
    {
        return Str::plural($attachment->name());
    }

    /**
     * Returns the style, or the default style if an empty style is supplied.
     *
     * @param AttachmentDataInterface $attachment
     * @param string                  $variant
     * @return string
     */
    protected function style(AttachmentDataInterface $attachment, $variant = '')
    {
        if ($variant) {
            return $variant;
        }

        return Arr::get($attachment->getConfig(), 'default-variant', FileHandler::ORIGINAL);
    }

    /**
     * Returns the attachment attribute name.
     *
     * @param AttachmentDataInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function getName(AttachmentDataInterface $attachment, $variant = '')
    {
        return $attachment->name();
    }


    /**
     * Utitlity function to turn a backslashed string into a string
     * suitable for use in a file path, e.g '\foo\bar' becomes 'foo/bar'.
     *
     * @param string $string
     * @return string
     */
    protected function handleBackslashes($string)
    {
        return str_replace('\\', '/', ltrim($string, '\\'));
    }

    /**
     * Utility method to ensure the input data only contains
     * printable characters. This is especially important when
     * handling non-printable ID's such as binary UUID's.
     *
     * @param mixed $input
     * @return mixed
     */
    protected function ensurePrintable($input)
    {
        if ( ! is_numeric($input) && ! ctype_print($input)) {
            // Hash the input data with SHA-256 to represent
            // as printable characters, with minimum chances
            // of the uniqueness being lost.
            return hash('sha256', $input);
        }

        return $input;
    }
}
