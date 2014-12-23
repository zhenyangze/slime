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
     * @param \Slime\Component\Support\Context $CTX
     *
     * @return void
     */
    public function setCTX($CTX);

    /**
     * @param string $sKey
     * @param mixed  $mDefault
     * @param bool   $bWithParse
     *
     * @return mixed
     */
    public function get($sKey, $mDefault = null, $bWithParse = false);

    /**
     * @param string $sKey
     * @param bool   $bWithParse
     *
     * @return mixed
     */
    public function getForce($sKey, $bWithParse = false);

    /**
     * @param string|array $mData
     * @param bool         $bForce
     *
     * @return mixed
     */
    public function parse($mData, $bForce);
}