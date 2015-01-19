<?php
namespace Slime\Component\Config;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
class Adaptor_PHP extends Adaptor_ABS
{
    /** @var string */
    protected $sCurrentDir;

    /** @var string */
    protected $sDefaultBaseDir;

    /** @var bool */
    protected $bIsDefault;

    /** @var array */
    protected $aCachedData;

    /**
     * @param string      $sBaseDir
     * @param string      $sCurrentDirName
     * @param null|string $nsDefaultDirName
     */
    public function __construct($sBaseDir, $sCurrentDirName, $nsDefaultDirName = null)
    {
        $this->sCurrentDir     = $sBaseDir . '/' . $sCurrentDirName;
        $this->sDefaultBaseDir = $nsDefaultDirName === null ? $this->sCurrentDir : $sBaseDir . '/' . $nsDefaultDirName;
        $this->bIsDefault      = $this->sCurrentDir === $this->sDefaultBaseDir;
    }

    /**
     * @param string $sKey
     * @param mixed  $mDefault
     * @param bool   $bForce
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get($sKey, $mDefault = null, $bForce = false)
    {
        if ($this->bIsDefault) {
            $mResult = $this->_find($sKey, $this->sCurrentDir);
        } else {
            $mResult = (($mCurResult = $this->_find($sKey, $this->sCurrentDir)) === null) ?
                $this->_find($sKey, $this->sDefaultBaseDir) : $mCurResult;
        }

        if ($mResult === null) {
            if ($bForce) {
                throw new \OutOfBoundsException("[CONFIG] ; Can not find key[$sKey] in config");
            } else {
                return $mDefault;
            }
        }

        return $this->parse($mResult, $mDefault, $bForce);
    }

    protected function _find($sKey, $sBaseDir)
    {
        if (strpos($sKey, '.') === false) {
            $sK    = $sKey;
            $aKeys = null;
        } else {
            $aKeys = explode('.', $sKey);
            $sK    = array_shift($aKeys);
        }

        if (!isset($this->aCachedData[$sBaseDir][$sK])) {
            $sConfFile                         = $sBaseDir . '/' . str_replace(':', '/', $sK) . '.php';
            $mResult                           = file_exists($sConfFile) ? require $sConfFile : null;
            $this->aCachedData[$sBaseDir][$sK] = $mResult;
        } else {
            $mResult = $this->aCachedData[$sBaseDir][$sK];
        }

        if ($aKeys !== null) {
            foreach ($aKeys as $sKey) {
                if (!isset($mResult[$sKey])) {
                    $mResult = null;
                    break;
                }
                $mResult = $mResult[$sKey];
            }
        }

        return $mResult;
    }
}