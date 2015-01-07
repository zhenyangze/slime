<?php
namespace Slime\Component\NoSQL\Redis;

use Slime\Bundle\Framework\InitBean;
use Slime\Component\Support\Sugar;

/**
 * Class Hook
 *
 * @package Slime\Component\NoSQL\Redis
 * @author  smallslime@gmail.com
 */
class Hook
{
    public static $aCB_CostAfter = array('Slime\\Component\\NoSQL\\Redis\\Hook', 'costAfter');

    /**
     * @param \Slime\Bundle\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(
                Redis::EV_CALL_BEFORE,
                Sugar::$aCB_LogTime, 0, null, array($B)
            )
            ->listen(
                Redis::EV_CALL_AFTER,
                self::$aCB_CostAfter, 0, null, array($B)
            );
    }

    public static function costAfter($Obj, $sMethod, $aArg, $Local, InitBean $B)
    {
        $fCost = round(microtime(true) - $Local['__RUN_AT__'], 4);
        if ((strlen($sArg = json_encode($aArg))) > 200) {
            $sArg = substr($sArg, 0, 100) . '...';
        }
        $B->getLog()->info("[REDIS] ; method : $sMethod ; argv : $sArg ; cost : $fCost");
    }
}