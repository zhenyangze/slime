<?php
namespace Slime\Component\Event;

/**
 * Class Event
 *
 * @package Slime\Component\Event
 * @author  smallslime@gmail.com
 */
class Event
{
    protected $aListener = array();
    protected $aSortedListener = array();

    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return bool
     */
    public function fire($sName, $aArgv = array())
    {
        if (!empty($this->aListener[$sName])) {
            foreach ($this->getSortedListeners($sName) as $aItem) {
                if (call_user_func_array(
                        $aItem[0],
                        empty($aItem[1]) ? $aArgv : array_merge($aArgv, $aItem[1])
                    ) === false
                ) {
                    break;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array|string $asName
     * @param mixed        $mCB
     * @param int          $iPriority
     * @param null|string  $nsSign
     * @param array        $aEvnVar
     *
     * @return Event
     */
    public function listen($asName, $mCB, $iPriority = 0, $nsSign = null, array $aEvnVar = array())
    {
        foreach ((array)$asName as $sName) {
            if (!empty($this->aSortedListener[$sName])) {
                $this->aSortedListener[$sName] = array();
            }
            if ($nsSign === null) {
                $this->aListener[$sName][$iPriority][] = array($mCB, $aEvnVar);
            } else {
                if (isset($this->aListener[$sName][$iPriority][$nsSign])) {
                    throw new \RuntimeException("[EVENT] ; Event[$sName.$nsSign] has exist");
                }
                $this->aListener[$sName][$iPriority][$nsSign] = array($mCB, $aEvnVar);
            }
        }

        return $this;
    }

    /**
     * @param $sName
     *
     * @return bool
     */
    public function hasListener($sName)
    {
        return isset($this->aListener[$sName]);
    }

    /**
     * @param string $sName
     *
     * @return array
     */
    public function getSortedListeners($sName)
    {
        if (empty($this->aSortedListener[$sName])) {
            krsort($this->aListener[$sName]);
            $this->aSortedListener[$sName] = call_user_func_array('array_merge', $this->aListener[$sName]);
        }

        return $this->aSortedListener[$sName];
    }

    /**
     * @param string      $sName
     * @param null|string $nsSign
     */
    public function forget($sName, $nsSign = null)
    {
        if ($nsSign === null) {
            if (isset($this->aListener[$sName])) {
                unset($this->aListener[$sName]);
            }
        } else {
            if (isset($this->aListener[$sName][$nsSign])) {
                unset($this->aListener[$sName][$nsSign]);
            }
        }
    }
}
