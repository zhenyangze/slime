<?php
namespace Slime\Component\View;

/**
 * Class Ext
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
class Ext
{
    /**
     * @param \Slime\Component\Event\Event $EV
     * @param \Slime\Component\Log\Logger  $Log
     */
    public static function ev_LogPHPRender($EV, $Log)
    {
        $EV->listen(Adaptor_PHP::EV_RENDER_BEFORE,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $Log->info("[VIEW] ; Render start ; file : {$Local['file']}");
                $Local['start'] = microtime(true);
            }
        );
        $EV->listen(Adaptor_PHP::EV_RENDER_AFTER,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $fCost = round(microtime(true) - $Local['start'], 4);
                $Log->info("[VIEW] ; Render finish ; cost : $fCost");
            }
        );
    }
}