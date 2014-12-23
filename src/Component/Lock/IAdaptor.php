<?php
namespace Slime\Component\Lock;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\Lock
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param int    $iExpire  (单位MS); >0:锁过期时间 / other:永不过期(null)
     * @param int    $iTimeout (单位MS); 获取锁失败后: 0:立刻返回false / >0 等待时间 / other:永久阻塞(null);
     *
     * @return bool
     */
    public function acquire($iExpire = null, $iTimeout = null);

    /**
     * @return bool
     */
    public function release();
}