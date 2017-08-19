<?php
namespace Czim\Paperclip\Path;

use Czim\FileHandling\Handler\FileHandler;
use Czim\Paperclip\Contracts\AttachmentInterface;
use Czim\Paperclip\Contracts\Path\InterpolatorInterface;

/**
 * Class Interpolator
 *
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
     * @param string              $string
     * @param AttachmentInterface $attachment
     * @param string|null         $variant
     * @return string
     */
    public function interpolate($string, AttachmentInterface $attachment, $variant = null)
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
            ':url'          => 'url',
            ':variant'      => 'style',
        ];
    }

    /**
     * Returns the file name.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     *
     * @return string
     */
    protected function filename(AttachmentInterface $attachment, $variant = '')
    {
        return $attachment->originalFilename();
    }

    /**
     * Generates the url to a file upload.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     *
     * @return string
     */
    protected function url(AttachmentInterface $attachment, $variant = '')
    {
        return $this->interpolate($attachment->url($variant), $attachment, $variant);
    }

    /**
     * Returns the application root of the project.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     *
     * @return string
     */
    protected function appRoot(AttachmentInterface $attachment, $variant = '')
    {
        return app_path();
    }

    /**
     * Returns the current class name, taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Swingline/Stapler.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function getClass(AttachmentInterface $attachment, $variant = '')
    {
        return $this->handleBackslashes($attachment->getInstanceClass());
    }

    /**
     * Returns the current class name, not taking into account namespaces, e.g.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function getClassName(AttachmentInterface $attachment, $variant = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());

        return end($classComponents);
    }

    /**
     * Returns the current class name, exclusively taking into account namespaces, e.g
     * 'Swingline\Stapler' will become Swingline.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     *
     * @return string
     */
    protected function getNamespace(AttachmentInterface $attachment, $variant = '')
    {
        $classComponents = explode('\\', $attachment->getInstanceClass());

        return implode('/', array_slice($classComponents, 0, count($classComponents) - 1));
    }

    /**
     * Returns the basename portion of the attached file, e.g 'file' for file.jpg.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function basename(AttachmentInterface $attachment, $variant = '')
    {
        return pathinfo($attachment->originalFilename(), PATHINFO_FILENAME);
    }

    /**
     * Returns the extension of the attached file, e.g 'jpg' for file.jpg.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function extension(AttachmentInterface $attachment, $variant = '')
    {
        return pathinfo($attachment->originalFilename(), PATHINFO_EXTENSION);
    }

    /**
     * Returns the id of the current object instance.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function id(AttachmentInterface $attachment, $variant = '')
    {
        return $this->ensurePrintable($attachment->getInstance()->getKey());
    }

    /**
     * Return a secure Bcrypt hash of the attachment's corresponding instance id.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function secureHash(AttachmentInterface $attachment, $variant = '')
    {
        return hash(
            'sha256',
            $this->id($attachment, $variant) . $attachment->size() . $attachment->originalFilename()
        );
    }

    /**
     * Return a Bcrypt hash of the attachment's corresponding instance id.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function hash(AttachmentInterface $attachment, $variant = '')
    {
        return hash('sha256', $this->id($attachment, $variant));
    }

    /**
     * Generates the id partition of a record, e.g /000/001/234 for an id of 1234.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function idPartition(AttachmentInterface $attachment, $variant = '')
    {
        $id = $this->ensurePrintable($attachment->getInstance()->getKey());

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
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function attachment(AttachmentInterface $attachment, $variant = '')
    {
        return str_plural($attachment->name());
    }

    /**
     * Returns the style, or the default style if an empty style is supplied.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function style(AttachmentInterface $attachment, $variant = '')
    {
        if ($variant) {
            return $variant;
        }

        return array_get($attachment->getConfig(), 'default-variant', FileHandler::ORIGINAL);
    }

    /**
     * Returns the attachment attribute name.
     *
     * @param AttachmentInterface $attachment
     * @param string              $variant
     * @return string
     */
    protected function getName(AttachmentInterface $attachment, $variant = '')
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
