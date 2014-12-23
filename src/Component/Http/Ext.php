<?php
namespace Slime\Component\Http;

/**
 * Class Ext
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class Ext
{
    /**
     * @param \Slime\Component\Event\Event $EV
     * @param \Slime\Component\Log\Logger  $Log
     */
    public static function ev_LogCost($EV, $Log)
    {
        $EV->listen(Call::EV_EXEC_BEFORE,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $Log->info("[HTTP] ; Call[$sMethod] start[{$Obj->nsUrl}]");
                $Local['start'] = microtime(true);
            }
        );
        $EV->listen(CALL::EV_EXEC_AFTER,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $fCost = round(microtime(true) - $Local['start'], 4);
                $Log->info("[HTTP] ; Call[$sMethod] finish ; cost : $fCost");
            }
        );
    }
}