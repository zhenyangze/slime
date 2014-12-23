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
            foreach ($this->getSortedListeners($sName) as $mCB) {
                if (call_user_func_array($mCB, $aArgv) === false) {
                    break;
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array | string $asName
     * @param mixed          $mCB
     * @param int            $iPriority
     */
    public function listen($asName, $mCB, $iPriority = 0)
    {
        foreach ((array)$asName as $sName) {
            if (!empty($this->aSortedListener[$sName])) {
                $this->aSortedListener[$sName] = array();
            }
            $this->aListener[$sName][$iPriority][] = $mCB;
        }
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
     * @param string $sName
     */
    public function forget($sName)
    {
        if (isset($this->aListener[$sName])) {
            $this->aListener[$sName] = array();
        }
    }
}
