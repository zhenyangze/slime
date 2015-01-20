<?php
namespace Slime\Component\Cache;

use Slime\Component\NoSQL\Redis\Redis;

/**
 * Class Adaptor_Redis
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_Redis implements IAdaptor
{
    public function __call($sMethod, $aParam)
    {
        return empty($aParam) ?
            $this->_getInst()->$sMethod() :
            call_user_func_array(array($this->_getInst(), $sMethod), $aParam);
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->_getInst()->get($sKey);
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
        return $this->_getInst()->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->_getInst()->del($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->_getInst()->flushDB();
    }


    /** @var \Redis */
    private $_nInst = null;

    /**
     * @param Redis $Redis
     */
    public function _setInst(Redis $Redis)
    {
        $this->_nInst = $Redis;
    }

    /**
     * @return \Redis
     */
    public function _getInst()
    {
        if ($this->_nInst === null) {
            throw new \RuntimeException('[Cache] ; Inst is not set before');
        }

        return $this->_nInst;
    }
}