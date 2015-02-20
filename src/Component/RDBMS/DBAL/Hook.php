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
    public static $aCB_CostAfter = array('Slime\\Component\\RDBMS\\DBAL\\Hook', 'costAfter');

    /**
     * @param \Slime\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(
                array(Engine::EV_PDO_RUN_BEFORE, Engine::EV_PDO_STMT_RUN_BEFORE),
                Sugar::$aCB_LogTime, 0, null, array($B)
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

    public static function costAfter($Obj, $sMethod, $aArg, $Local, InitBean $B, $sK)
    {
        $fCost = round(microtime(true) - $Local['__RUN_AT__'], 4);
        $sArg = json_encode($aArg);
        $B->getLog()->info("[DBAL] ; method : [$sK.$sMethod] ; argv : $sArg ; cost : $fCost");
    }
}