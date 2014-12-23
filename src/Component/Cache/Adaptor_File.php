<?php
namespace Slime\Component\Cache;

/**
 * Class Adaptor_File
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_File implements IAdaptor
{
    protected $sCachePath;
    protected $mCBKey2File;

    /**
     * @param string $sCacheDir
     * @param int    $iCreateMode
     * @param mixed  $mCBKey2File
     *
     * @throws \RuntimeException
     */
    public function __construct($sCacheDir, $iCreateMode = 0777, $mCBKey2File = null)
    {
        $this->sCachePath = rtrim($sCacheDir, '/') . '/';
        if (!file_exists($this->sCachePath)) {
            if (!@mkdir($this->sCachePath, $iCreateMode, true)) {
                throw new \RuntimeException("[CACHE] : Create dir[$sCacheDir] failed");
            }
        }
        $this->mCBKey2File = $mCBKey2File;
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        $sFile = $this->sCachePath .
            ($this->mCBKey2File === null ? md5($sKey) : call_user_func($this->mCBKey2File, $sKey));

        if (!file_exists($sFile)) {
            return null;
        }

        $aData = file($sKey);
        if (count($aData) !== 2) {
            return null;
        }

        if (time() - $aData[0] > 0) {
            $this->delete($sKey);
            return null;
        }
        return json_decode($aData[1]);
    }

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iExpire
     *
     * @return bool
     */
    public function set($sKey, $mValue, $iExpire)
    {
        return file_put_contents(
            $this->sCachePath . ($this->mCBKey2File === null ? md5($sKey) : call_user_func($this->mCBKey2File, $sKey)),
            sprintf("%d\n%s", time() + $iExpire, json_encode($mValue))
        ) !== false;
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        $sFile = $this->sCachePath . md5($sKey);
        return file_exists($sFile) ? unlink($sFile) : true;
    }

    /**
     * @return bool
     */
    public function flush()
    {
        $rDir = opendir($this->sCachePath);
        while (($sFile = readdir($rDir)) !== false) {
            if (ltrim($sFile, '.') !== '') {
                unlink($this->sCachePath . $sFile);
            }
        }
        closedir($rDir);
        return true;
    }
}