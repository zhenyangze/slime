<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http\REQ;

class Hook
{
    public static $aCB_Register = array('Slime\\Bundle\\Framework\\Hook', 'register');
    public static $aCB_PreLog = array('Slime\\Bundle\\Framework\\Hook', 'preLog');
    public static $aCB_AfterLog = array('Slime\\Bundle\\Framework\\Hook', 'afterLog');
    public static $aCB_DestroyLog = array('Slime\\Bundle\\Framework\\Hook', 'destroyLog');

    /**
     * @param \Slime\Bundle\Framework\InitBean $B
     */
    public static function register($B)
    {
        $B->Event
            ->listen(Bootstrap::EV_PRE_RUN, self::$aCB_PreLog)
            ->listen(Bootstrap::EV_AFTER_RUN, self::$aCB_AfterLog)
            ->listen(Bootstrap::EV_DESTROY, self::$aCB_DestroyLog)
        ;
    }

    /**
     * @param \Slime\Bundle\Framework\Bootstrap $B
     * @param \ArrayObject                      $Local
     */
    public static function preLog($B, $Local)
    {
        $Local['__START_TIME__'] = microtime(true);
        if (($REQ = $B->CTX->getIgnore('REQ'))!==null && $REQ instanceof REQ) {
            $sLog = sprintf('[SYSTEM] ; system run start ; url : %s ; ip : %s',
                $REQ->getUrl(), $REQ->guessClientIP()
            );
        } elseif (!empty(($aArgv = $B->CTX->getIgnore('aArgv')))) {
            $sLog = sprintf('[SYSTEM] ; system run start ; argv : %s', json_encode($aArgv));
        } else {
            $sLog = sprintf('[SYSTEM] ; system run start');
        }
        $B->Log->info($sLog);
    }

    /**
     * @param \Slime\Bundle\Framework\Bootstrap $B
     * @param \ArrayObject                      $Local
     */
    public static function afterLog($B, $Local)
    {
        $B->Log->info('[SYSTEM] ; system run finish');
    }

    /**
     * @param \Slime\Bundle\Framework\Bootstrap $B
     * @param \ArrayObject                      $Local
     */
    public static function destroyLog($B, $Local)
    {
        $B->Log->info(
            sprintf(
                '[SYSTEM] ; total count ; cost : %ss ; mem usage %s/%s ; mem top usage %s/%s',
                round(microtime(true) - $Local['__START_TIME__'], 4),
                \Slime\Component\Support\File::autoFormatSize(memory_get_usage()),
                \Slime\Component\Support\File::autoFormatSize(memory_get_usage(true)),
                \Slime\Component\Support\File::autoFormatSize(memory_get_peak_usage()),
                \Slime\Component\Support\File::autoFormatSize(memory_get_peak_usage(true))
            )
        );
    }
}