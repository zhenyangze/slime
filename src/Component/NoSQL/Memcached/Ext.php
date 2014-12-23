<?php
namespace Slime\Component\NoSQL\Memcached;

/**
 * Class Ext
 *
 * @package Slime\Component\Cache
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
        $EV->listen(Memcached::EV_CALL_BEFORE,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $Log->info("[MC] ; Call[$sMethod] start");
                $Local['start'] = microtime(true);
            }
        );
        $EV->listen(Memcached::EV_CALL_AFTER,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $fCost = round(microtime(true) - $Local['start'], 4);
                $Log->info("[MC] ; Call[$sMethod] finish ; cost : $fCost");
            }
        );
    }
}