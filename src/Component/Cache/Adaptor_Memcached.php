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
    /**
     * @var \Memcached
     */
    protected $nInst = null;

    public function __call($sMethod, $aParam)
    {
        return empty($aParam) ?
            $this->getInst()->$sMethod() :
            call_user_func_array(array($this->getInst(), $sMethod), $aParam);
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->getInst()->get($sKey);
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
        return $this->getInst()->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->getInst()->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->getInst()->flush();
    }

    /**
     * @param Memcached $Memcached
     */
    public function setInst(Memcached $Memcached)
    {
        $this->nInst = $Memcached;
    }

    /**
     * @return \Memcached
     */
    public function getInst()
    {
        if ($this->nInst === null) {
            throw new \RuntimeException('[Cache] ; Inst is not set before');
        }

        return $this->nInst;
    }
}