<?php
namespace Slime\Framework;

use Slime\Component\Http\REQ;

class Hook
{
    public static $aCB_Register = array('Slime\\Framework\\Hook', 'register');
    public static $aCB_PreLog = array('Slime\\Framework\\Hook', 'preLog');
    public static $aCB_AfterLog = array('Slime\\Framework\\Hook', 'afterLog');
    public static $aCB_DestroyLog = array('Slime\\Framework\\Hook', 'destroyLog');

    /**
     * @param \Slime\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(Bootstrap::EV_PRE_RUN, self::$aCB_PreLog)
            ->listen(Bootstrap::EV_AFTER_RUN, self::$aCB_AfterLog)
            ->listen(Bootstrap::EV_DESTROY, self::$aCB_DestroyLog);
    }

    /**
     * @param \Slime\Framework\Bootstrap $B
     * @param \ArrayObject               $Local
     */
    public static function preLog($B, $Local)
    {
        $Local['__START_TIME__'] = microtime(true);
        $B->Log->info('[SYSTEM] ; system run start');
    }

    /**
     * @param \Slime\Framework\Bootstrap $B
     * @param \ArrayObject               $Local
     */
    public static function afterLog($B, $Local)
    {
        $B->Log->info('[SYSTEM] ; system run finish');
    }

    /**
     * @param \Slime\Framework\Bootstrap $B
     * @param \ArrayObject               $Local
     */
    public static function destroyLog($B, $Local)
    {
        if (($REQ = $B->CTX->getIgnore('REQ')) !== null && $REQ instanceof REQ) {
            $sLog = sprintf(' url : %s ; ip : %s ;',
                $REQ->getUrl(), $REQ->guessClientIP()
            );
        } elseif (($aArgv = $B->CTX->getIgnore('aArgv')) !== null) {
            $sLog = sprintf(' argv : %s ;', json_encode($aArgv));
        } else {
            $sLog = '';
        }

        $B->Log->info(
            sprintf(
                '[SYSTEM] ; total info ;%s cost : %ss ; mem usage : %s/%s ; mem top usage : %s/%s',
                $sLog,
                round(microtime(true) - $Local['__START_TIME__'], 4),
                \Slime\Component\Support\File::autoFormatSize(memory_get_usage()),
                \Slime\Component\Support\File::autoFormatSize(memory_get_usage(true)),
                \Slime\Component\Support\File::autoFormatSize(memory_get_peak_usage()),
                \Slime\Component\Support\File::autoFormatSize(memory_get_peak_usage(true))
            )
        );
    }
}