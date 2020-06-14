<?php
/**
 * bahadorbzd
 * 01/06/2020 11:06
 */

namespace Czim\Paperclip\ImgProxy;


use Exception;

class ImgProxyService
{
    private string $salt;
    private string $key;
    private string $signatureSize;
    private string $imageWith;
    private string $imageHeight;
    private string $url;
    private int $enlarge = 1;
    private string $watermark;
    private string $gravity = 'no';
    private string $resizeType = 'fit';
    private string $imgProxyUri;
    private string $fileServerUri;

    /**
     * ImgProxyService constructor.
     * @param string $key
     * @param string $salt
     * @param string $signatureSize
     * @param string $imgProxyUri
     * @param string $fileServerUri
     */
    public function __construct(string $key, string $salt, string $signatureSize, string $imgProxyUri, string $fileServerUri)
    {
        $this->key = $key;
        $this->salt = $salt;
        $this->signatureSize = $signatureSize;
        $this->imgProxyUri = rtrim($imgProxyUri, '/') . "/";
        $this->fileServerUri = $fileServerUri;
    }

    /**
     * @param string $gravity
     * @return $this
     */
    public function setGravity(string $gravity)
    {
        $this->gravity = $gravity;
        return $this;
    }

    /**
     * @param string $resizeType
     * @return $this
     */
    public function setResizeType(string $resizeType)
    {
        $this->resizeType = $resizeType;
        return $this;
    }

    /**≤
     * @param $opacity
     * @param string $position
     * @param null $scale
     * @param null $xOffset
     * @param null $yOffset
     * @return $this
     */
    public function setWatermark($opacity, $position = null, $scale = null, $xOffset = null, $yOffset = null)
    {
        $watermark = "/wm:{$opacity}";
        if ($position)
            $watermark .= ":{$position}";
        if ($scale)
            $watermark .= ":{$scale}";
        if ($xOffset)
            $watermark .= ":{$xOffset}";
        if ($yOffset)
            $watermark .= ":{$yOffset}";

        $this->watermark = $watermark;
        return $this;
    }

    /**
     * @param $url
     * @param $width
     * @param $height
     * @param int $enlarge
     * @return string
     * @throws Exception
     */
    public function resize()
    {
        if (!$this->url)
            throw new Exception("you most set image url");
        if (!$this->imageWith)
            throw new Exception("you most set image with");
        if (!$this->imageHeight)
            throw new Exception("you most set image height");

        $encodedUrl = rtrim(strtr(base64_encode($this->url), '+/', '-_'), '=');

        $path = "/rs:{$this->resizeType}:{$this->imageWith}:{$this->imageHeight}:{$this->enlarge}/g:{$this->gravity}";

        if (isset($this->watermark))
            $path .= $this->watermark;

        $path .= "/{$encodedUrl}.{$this->getExtension($this->url)}";

        if (!$this->key || !$this->salt) {
            return $this->imgProxyUri . $path;
        }

        $signature = hash_hmac('sha256', $this->getSaltBin() . $path, $this->getKeyBin(), true);
        $signature = pack('A' . $this->signatureSize, $signature);
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return $this->imgProxyUri . $signature . $path;
    }

    /**
     * @param $url
     * @return string
     */
    private function getExtension($url)
    {
        $pathInfo = pathinfo($url);
        return $pathInfo['extension'];
    }

    /**
     * @return false|string
     * @throws Exception
     */
    private function getSaltBin()
    {
        if (!$this->salt)
            return null;

        $saltBin = pack("H*", $this->salt);
        if (empty($saltBin)) {
            throw new Exception("Salt expected to be hex-encoded string");
        }
        return $saltBin;
    }

    /**
     * @return false|string
     * @throws Exception
     */
    private function getKeyBin()
    {
        if (!$this->key)
            return null;

        $keyBin = pack("H*", $this->key);
        if (empty($keyBin)) {
            throw new Exception("Key expected to be hex-encoded string");
        }
        return $keyBin;
    }

    /**
     * @param string $imageWith
     * @return $this
     */
    public function setImageWith(string $imageWith)
    {
        $this->imageWith = $imageWith;
        return $this;
    }

    /**
     * @param string $imageHeight
     * @return $this
     */
    public function setImageHeight(string $imageHeight)
    {
        $this->imageHeight = $imageHeight;
        return $this;
    }

    /**
     * @param string $url
     * @return string
     * @throws Exception
     */
    public function setUrl(string $url)
    {
        $normalizeUrl = $this->normalizeUri($url);
        if ($normalizeUrl != $this->normalizeUri($this->fileServerUri))
            throw new Exception("file url and file server url most be same");
        $parsedUrl = parse_url($url);

        if ($parsedUrl["scheme"] != 's3')
            $this->url = "s3:/" . $parsedUrl["path"];
        else
            $this->url = $url;
        return $this;
    }

    private function normalizeUri($uri)
    {
        $parse = parse_url($uri);
        $host = str_ireplace('www.', '', $parse['host']);
        return $parse['scheme'] . "//" . $host;
    }

    /**
     * @param int $enlarge
     * @return $this
     */
    public function setEnlarge(int $enlarge)
    {
        $this->enlarge = $enlarge;
        return $this;
    }

    /**
     * @param string $imgProxyUri
     * @return $this
     */
    public function setImgProxyUri(string $imgProxyUri)
    {
        $this->imgProxyUri = $imgProxyUri;
        return $this;
    }

    /**
     * @param string $s3Uri
     * @return $this
     */
    public function setS3Uri(string $s3Uri)
    {
        $this->s3Uri = $s3Uri;
        return $this;
    }

    /**
     * @param string $fileServerUri
     * @return $this
     */
    public function setFileServerUri(string $fileServerUri)
    {
        $this->fileServerUri = $fileServerUri;
        return $this;
    }
}