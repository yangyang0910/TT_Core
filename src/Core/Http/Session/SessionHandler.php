<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/11/9
 * Time: 下午12:26
 */

namespace Core\Http\Session;

use Core\Component\IO\FileIO;
use Core\Utility\File;

/**
 * Class SessionHandler
 * @package Core\Http\Session
 */
class SessionHandler implements \SessionHandlerInterface
{
    /**
     * @var
     */
    private $sessionName;
    /**
     * @var
     */
    private $savePath;
    /**
     * @var
     */
    private $fileStream;
    /**
     * @var
     */
    private $saveFile;

    /**
     * @return bool
     */
    public function close()
    {
        if ($this->fileStream instanceof FileIO) {
            if ($this->fileStream->getStreamResource()) {
                $this->fileStream->unlock();
            }
            $this->fileStream = NULL;
            return TRUE;
        } else {
            return TRUE;
        }
    }

    /**
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        $this->close();
        if (file_exists($this->saveFile)) {
            unlink($this->saveFile);
        }
        return TRUE;
    }

    /**
     * @param int $maxlifetime
     * @return bool|void
     */
    public function gc($maxlifetime)
    {
        $current = time();
        $res     = File::scanDir($this->savePath);
        if (is_array($res)) {
            foreach ($res as $file) {
                $time = fileatime($file);
                if ($current - $time > $maxlifetime) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool
     */
    public function open($save_path, $name)
    {
        $this->savePath    = $save_path;
        $this->sessionName = $name;
        return TRUE;
    }

    /**
     * @param string $session_id
     * @return string
     */
    public function read($session_id)
    {
        if (!$this->fileStream) {
            $this->saveFile   = $this->savePath . "/{$this->sessionName}_{$session_id}";
            $this->fileStream = new FileIO($this->saveFile);
        }
        if (!$this->fileStream->getStreamResource()) {
            return '';
        } else {
            $this->fileStream->lock();
            return $this->fileStream->__toString();
        }
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool|int
     */
    public function write($session_id, $session_data)
    {
        if (!$this->fileStream) {
            $this->fileStream = new FileIO($this->saveFile);
        }
        if (!$this->fileStream->getStreamResource()) {
            return FALSE;
        } else {
            $this->fileStream->truncate();
            $this->fileStream->rewind();
            return $this->fileStream->write($session_data);
        }
    }
}