<?php
namespace Slime\Component\Cache;

/**
 * Class Adaptor_Redis
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_Redis implements IAdaptor
{
    /** @var array */
    public $aConfig;

    /** @var \Redis */
    private $Inst;

    /**
     * @param \Slime\Component\NoSQL\Redis\Redis $Inst
     */
    public function __construct($Inst)
    {
        $this->Inst = $Inst;
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
        return $this->Inst->flushDB();
    }
}