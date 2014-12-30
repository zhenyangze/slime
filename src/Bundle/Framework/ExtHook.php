<?php
namespace Slime\Bundle\Framework;

class ExtHook
{
    /**
     * @param \Slime\Bundle\Framework\Bootstrap $B
     * @param \ArrayObject                      $Local
     */
    public static function PRE_RUN_COST($B, $Local)
    {
        $Local['__START_TIME__'] = microtime(true);
        $B->Log->info('[SYSTEM] ; system run start');
    }

    /**
     * @param \Slime\Bundle\Framework\Bootstrap $B
     * @param \ArrayObject                      $Local
     */
    public static function AFTER_RUN_LOG($B, $Local)
    {
        $B->Log->info('[SYSTEM] ; system run finish');
    }

    /**
     * @param \Slime\Bundle\Framework\Bootstrap $B
     * @param \ArrayObject                      $Local
     */
    public static function DESTROY_COST($B, $Local)
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