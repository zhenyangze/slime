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
    /** @var \Redis */
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
        return $this->getInst()->del($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->getInst()->flushDB();
    }

    /**
     * @param Redis $Redis
     */
    public function setInst(Redis $Redis)
    {
        $this->nInst = $Redis;
    }

    /**
     * @return \Redis
     */
    public function getInst()
    {
        if ($this->nInst === null) {
            throw new \RuntimeException();
        }

        return $this->nInst;
    }
}