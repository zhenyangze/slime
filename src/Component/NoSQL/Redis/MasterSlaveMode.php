<?php
namespace Slime\Component\NoSQL\Redis;

/**
 * Class MasterSlaveMode
 *
 * @package Slime\Component\NoSQL\Redis
 * @author  smallslime@gmail.com
 */
class MasterSlaveMode
{
    public static $aCB_CommonMode = array('Slime\\Component\\NoSQL\\Redis\\MasterSlaveMode', 'commonMode');

    public static function commonMode($sMethod)
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