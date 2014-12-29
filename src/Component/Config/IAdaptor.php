<?php
namespace Slime\Component\Config;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param string $sKey
     * @param mixed  $mDefault
     * @param bool   $bForce
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get($sKey, $mDefault = null, $bForce = false);
}