<?php
namespace Slime\Component\Http;

use Slime\Bundle\Framework\InitBean;
use Slime\Component\Support\Sugar;

/**
 * Class Hook
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Hook
{
    public static $aCB_CostAfter = array('Slime\\Component\\Http\\Hook', 'costAfter');

    /**
     * @param \Slime\Bundle\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(
                Call::EV_EXEC_BEFORE,
                Sugar::$aCB_LogTime, 0, null, array($B)
            )
            ->listen(
                Call::EV_EXEC_AFTER,
                self::$aCB_CostAfter, 0, null, array($B)
            );
    }

    public static function costAfter($Obj, $sMethod, $aArg, $Local, InitBean $B, $sK)
    {
        $fCost = round(microtime(true) - $Local['__RUN_AT__'], 4);
        $B->getLog()->info("[HTTP] ; Call[$sMethod] ; url : {$Obj->nsUrl} ; cost : $fCost");
    }
}