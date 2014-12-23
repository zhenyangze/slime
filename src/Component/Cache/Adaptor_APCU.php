<?php
namespace Slime\Component\Cache;

/**
 * Class Adaptor_APCU
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
class Adaptor_APCU implements IAdaptor
{
    public function __construct()
    {
    }

    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey)
    {
        return apc_fetch($sKey);
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
        return apc_store($sKey, $mValue, $iExpire);
    }

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey)
    {
        return apc_delete($sKey);
    }

    /**
     * @return bool
     */
    public function flush()
    {
        return apc_clear_cache();
    }

    /**
     * @return array|bool
     */
    public function showInfo()
    {
        return apc_sma_info();
    }
}