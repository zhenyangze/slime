<?php
namespace Slime\Component\View;

use Slime\Bundle\Framework\InitBean;
use Slime\Component\Support\Sugar;

/**
 * Class Hook
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
class Hook
{
    public static $aCB_CostAfter = array('Slime\\Component\\View\\Hook', 'costAfter');

    /**
     * @param \Slime\Bundle\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(
                Adaptor_PHP::EV_RENDER_BEFORE,
                Sugar::$aCB_LogTime, 0, null, array($B)
            )
            ->listen(
                Adaptor_PHP::EV_RENDER_AFTER,
                self::$aCB_CostAfter, 0, null, array($B)
            );
    }

    public static function costAfter($Obj, $sMethod, $aArg, $Local, InitBean $B)
    {
        $fCost = round(microtime(true) - $Local['__RUN_AT__'], 4);
        $B->getLog()->info("[VIEW] ; tpl : {$Local['__TPL__']} ; cost : $fCost");
    }
}