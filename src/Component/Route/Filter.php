<?php
namespace Slime\Component\Route;

/**
 * Class Filter
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class Filter
{
    /**
     * @param \Slime\Component\Http\REQ $REQ
     *
     * @return bool
     */
    public static function isGET($REQ)
    {
        return $REQ->getMethod() === 'GET';
    }

    /**
     * @param \Slime\Component\Http\REQ $REQ
     *
     * @return bool
     */
    public static function isPOST($REQ)
    {
        return $REQ->getMethod() === 'POST';
    }

    /**
     * @param \Slime\Component\Http\REQ $REQ
     * @param string                    $sHOST
     *
     * @return bool
     */
    public static function matchHOST($REQ, $sHOST)
    {
        return $REQ->getHeader('Host') === $sHOST;
    }
}