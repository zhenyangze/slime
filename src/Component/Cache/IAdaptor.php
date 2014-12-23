<?php
namespace Slime\Component\Cache;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\Cache
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param string $sKey
     *
     * @return mixed
     */
    public function get($sKey);

    /**
     * @param string $sKey
     * @param mixed  $mValue
     * @param int    $iExpire
     *
     * @return bool
     */
    public function set($sKey, $mValue, $iExpire);

    /**
     * @param $sKey
     *
     * @return bool
     */
    public function delete($sKey);

    /**
     * @return bool
     */
    public function flush();
}