<?php
namespace Slime\Component\RDBMS\DBAL;

use Slime\Framework\InitBean;
use Slime\Component\Support\Sugar;

/**
 * Class Hook
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class Hook
{
    public static $aCB_Register = array('Slime\\Component\\RDBMS\\DBAL\\Hook', 'register');
    public static $aCB_CostBefore = array('Slime\\Component\\RDBMS\\DBAL\\Hook', 'costBefore');
    public static $aCB_CostAfter = array('Slime\\Component\\RDBMS\\DBAL\\Hook', 'costAfter');

    /**
     * @param \Slime\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(
                Engine::EV_PDO_RUN_BEFORE,
                self::$aCB_CostBefore, 0, null, array($B, 'PDO')
            )
            ->listen(
                Engine::EV_PDO_STMT_RUN_BEFORE,
                self::$aCB_CostBefore, 0, null, array($B, 'STMT')
            )
            ->listen(
                Engine::EV_PDO_RUN_AFTER,
                self::$aCB_CostAfter, 0, null, array($B, 'PDO')
            )
            ->listen(
                Engine::EV_PDO_STMT_RUN_AFTER,
                self::$aCB_CostAfter, 0, null, array($B, 'STMT')
            );
    }

    public static function costBefore($Obj, $sMethod, $aArg, $Local, InitBean $B, $sK)
    {
        $sTS  = microtime(true);
        $aArr = explode('.', $sTS);
        if (count($aArr) == 2) {
            $sSec = $aArr[0];
            $sMS  = $aArr[1];
        } else {
            $sSec = $aArr[0];
            $sMS  = '0';
        }
        $sID                 = base64_encode(pack('LL', $sMS, $sSec));
        $Local['__RUN_AT__'] = $sTS;
        $Local['__ID__']     = $sID;
        $sArg                = json_encode($aArg);
        $B->getLog()->info("[DBAL] ; id : $sID ; method : [$sK.$sMethod] ; argv : $sArg");
    }

    public static function costAfter($Obj, $sMethod, $aArg, $Local, InitBean $B, $sK)
    {
        $fCost = round(microtime(true) - $Local['__RUN_AT__'], 4);
        $B->getLog()->info("[DBAL] ; id : {$Local['__ID__']} ; finish ; cost : $fCost");
    }
}