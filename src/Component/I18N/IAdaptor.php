<?php
namespace Slime\Component\I18N;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param string $sKey
     *
     * @return string
     */
    public function get($sKey);
}