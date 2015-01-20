<?php
namespace Slime\Component\Cache;

use Slime\Component\NoSQL\Memcached\Memcached;

/**
 * Class Adaptor_Memcached
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_Memcached implements IAdaptor
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
        return $this->_getInst()->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->_getInst()->flush();
    }


    /**
     * @var \Memcached
     */
    private $_nInst = null;

    /**
     * @param Memcached $Memcached
     */
    public function _setInst(Memcached $Memcached)
    {
        $this->_nInst = $Memcached;
    }

    /**
     * @return \Memcached
     */
    public function _getInst()
    {
        if ($this->_nInst === null) {
            throw new \RuntimeException('[Cache] ; Inst is not set before');
        }

        return $this->_nInst;
    }
}