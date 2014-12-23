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
    protected $Inst = null;

    /**
     * @param Memcached $Obj
     */
    public function __construct($Obj)
    {
        $this->Inst = $Obj;
    }

    public function __call($sMethod, $aParam)
    {
        return empty($aParam) ? $this->Inst->$sMethod() : call_user_func_array(array($this->Inst, $sMethod), $aParam);
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return $this->Inst->get($sKey);
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
        return $this->Inst->set($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return $this->Inst->delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return $this->Inst->flush();
    }
}