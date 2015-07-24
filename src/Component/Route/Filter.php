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
     * @param \Slime\Component\Http\REQ        $REQ
     * @param \Slime\Component\Http\RESP       $RESP
     * @param \Slime\Component\Support\Context $CTX
     * @param string|array                     $m_s_aHOST
     *
     * @return bool
     */
    public static function matchHOST($REQ, $RESP, $CTX, $m_s_aHOST)
    {
        $sHost = $REQ->getHeader('Host');
        return is_array($m_s_aHOST) ? in_array($sHost, $m_s_aHOST) : $m_s_aHOST == $sHost;
    }
}
