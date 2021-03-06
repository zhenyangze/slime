<?php
namespace Slime\Component\NoSQL\Memcached;

use Slime\Framework\InitBean;
use Slime\Component\Support\Sugar;

/**
 * Class Hook
 *
 * @package Slime\Component\NoSQL\Memcached
 * @author  smallslime@gmail.com
 */
class Hook
{
    public static $aCB_Register = array('Slime\\Component\\NoSQL\\Memcached\\Hook', 'register');
    public static $aCB_CostAfter = array('Slime\\Component\\NoSQL\\Memcached\\Hook', 'costAfter');

    /**
     * @param \Slime\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(
                Memcached::EV_CALL_BEFORE,
                Sugar::$aCB_LogTime, 0, null, array($B)
            )
            ->listen(
                Memcached::EV_CALL_AFTER,
                self::$aCB_CostAfter, 0, null, array($B)
            );
    }

    public static function costAfter($Obj, $sMethod, $aArg, $Local, InitBean $B)
    {
        $fCost = round(microtime(true) - $Local['__RUN_AT__'], 4);
        $sArg = json_encode($aArg);
        $B->getLog()->info("[MC] ; method : $sMethod ; argv : $sArg ; cost : $fCost");
    }
}