<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/6/14
 * Time: 下午12:32
 */

namespace Core\Http\Message;


/**
 * Class UploadFile
 * @package Core\Http\Message
 */
class UploadFile
{
    /**
     * @var Stream
     */
    private $stream;
    /**
     * @var
     */
    private $size;
    /**
     * @var
     */
    private $error;
    /**
     * @var null
     */
    private $clientFileName;
    /**
     * @var null
     */
    private $clientMediaType;

    /**
     * UploadFile constructor.
     * @param      $tempName
     * @param      $size
     * @param      $errorStatus
     * @param null $clientFilename
     * @param null $clientMediaType
     */
    function __construct($tempName, $size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        $this->stream          = new Stream(fopen($tempName, "r+"));
        $this->error           = $errorStatus;
        $this->size            = $size;
        $this->clientFileName  = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * @return Stream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param $targetPath
     * @return bool
     */
    public function moveTo($targetPath)
    {
        return file_put_contents($targetPath, $this->stream) ? true : false;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return null
     */
    public function getClientFilename()
    {
        return $this->clientFileName;
    }

    /**
     * @return null
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }
}