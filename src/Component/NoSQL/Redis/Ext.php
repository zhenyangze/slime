<?php
namespace Slime\Component\NoSQL\Redis;

/**
 * Class Event_Register
 *
 * @package Slime\Component\NoSQL\Redis
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
        $EV->listen(Redis::EV_CALL_BEFORE,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $Log->info("[REDIS] ; Call[$sMethod] start");
                $Local['start'] = microtime(true);
            }
        );
        $EV->listen(Redis::EV_CALL_AFTER,
            function ($Obj, $sMethod, $aArg, $Local) use ($Log) {
                $fCost = round(microtime(true) - $Local['start'], 4);
                $Log->info("[REDIS] ; Call[$sMethod] finish ; cost : $fCost");
            }
        );
    }

    public static function cb_MasterSlave($sMethod)
    {
        static $aReadCMD = array(
            'EXISTS'           => true,
            'GET'              => true,
            'GETBIT'           => true,
            'GETRANGE'         => true,
            'HGET'             => true,
            'HGETALL'          => true,
            'HKEYS'            => true,
            'HLEN'             => true,
            'HMGET'            => true,
            'HVALS'            => true,
            'INFO'             => true,
            'KEYS'             => true,
            'LINDEX'           => true,
            'LLEN'             => true,
            'LRANGE'           => true,
            'MGET'             => true,
            'PTTL  '           => true,
            'SCARD'            => true,
            'SISMEMBER'        => true,
            'SMEMBERS'         => true,
            'SRANDMEMBER'      => true,
            'STRLEN'           => true,
            'TTL'              => true,
            'TYPE'             => true,
            'ZCARD'            => true,
            'ZCOUNT'           => true,
            'ZLEXCOUNT'        => true,
            'ZRANGE'           => true,
            'ZRANGEBYLEX'      => true,
            'ZRANGEBYSCORE'    => true,
            'ZRANK'            => true,
            'ZREVRANGE'        => true,
            'ZREVRANGEBYSCORE' => true,
            'ZREVRANK'         => true,
            'ZSCORE'           => true,
            'SCAN'             => true,
            'SSCAN'            => true,
            'HSCAN'            => true,
            'ZSCAN'            => true,
        );

        return isset($aReadCMD[$sMethod]) ? 'slave' : 'master';
    }
}