<?php
namespace Slime\Component\Lock;

/**
 * Class Adaptor_Redis
 *
 * @package Slime\Component\Lock
 * @author  smallslime@gmail.com
 */
class Adaptor_Redis implements IAdaptor
{
    /** @var \Redis */
    protected $Redis;

    /**
     * @param \Slime\Component\NoSQL\Redis\Redis|\Redis $Redis
     * @param string                                    $sLockKey
     * @param int                                       $iLockRetryLoopMS
     */
    public function __construct($Redis, $sLockKey, $iLockRetryLoopMS = 10)
    {
        $this->Redis            = $Redis;
        $this->sLockKey         = $sLockKey;
        $this->iLockRetryLoopUS = $iLockRetryLoopMS * 10000;
    }

    /**
     * @param int    $iExpire  (单位MS); >0:锁过期时间 / other:永不过期(null)
     * @param int    $iTimeout (单位MS); 获取锁失败后: 0:立刻返回false / >0 等待时间 / other:永久阻塞(null);
     *
     * @return bool
     */
    public function acquire($iExpire = null, $iTimeout = null)
    {
        if ($iTimeout === 0) {
            $bRS = $this->Redis->setnx($this->sLockKey, 1);
        } elseif ($iTimeout > 0) {
            $iT1 = microtime(true);
            do {
                $bRS = $this->Redis->setnx($this->sLockKey, 1);
                if ($bRS || (microtime(true) - $iT1 > $iTimeout)) {
                    break;
                }
                usleep($this->iLockRetryLoopUS);
            } while (true);
        } else {
            do {
                $bRS = $this->Redis->setnx($this->sLockKey, 1);
                if ($bRS) {
                    break;
                }
                usleep($this->iLockRetryLoopUS);
            } while (true);
        }

        if ($iExpire > 0 && $bRS) {
            $this->Redis->pExpire($this->sLockKey, $iExpire);
        }

        return $bRS;
    }

    /**
     * @return bool
     */
    public function release()
    {
        return $this->Redis->del($this->sLockKey);
    }
}