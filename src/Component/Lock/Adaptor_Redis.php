<?php
namespace Slime\Component\Lock;

use Slime\Component\NoSQL\Redis\Redis;

/**
 * Class Adaptor_Redis
 *
 * @package Slime\Component\Lock
 * @author  smallslime@gmail.com
 */
class Adaptor_Redis implements IAdaptor
{
    /**
     * @param string $sLockKey
     * @param int    $iLockRetryLoopMS
     */
    public function __construct($sLockKey, $iLockRetryLoopMS = 10)
    {
        $this->sLockKey         = $sLockKey;
        $this->iLockRetryLoopUS = $iLockRetryLoopMS * 10000;
    }

    /**
     * @param int $iExpire  (单位MS); >0:锁过期时间 / other:永不过期(null)
     * @param int $iTimeout (单位MS); 获取锁失败后: 0:立刻返回false / >0 等待时间 / other:永久阻塞(null);
     *
     * @return bool
     */
    public function acquire($iExpire = null, $iTimeout = null)
    {
        $Inst = $this->_getInst();

        if ($iTimeout === 0) {
            $bRS = $Inst->setnx($this->sLockKey, 1);
        } elseif ($iTimeout > 0) {
            $iT1 = microtime(true);
            do {
                $bRS = $Inst->setnx($this->sLockKey, 1);
                if ($bRS || ((microtime(true) - $iT1) * 1000 > $iTimeout)) {
                    break;
                }
                usleep($this->iLockRetryLoopUS);
            } while (true);
        } else {
            do {
                $bRS = $Inst->setnx($this->sLockKey, 1);
                if ($bRS) {
                    break;
                }
                usleep($this->iLockRetryLoopUS);
            } while (true);
        }

        if ($iExpire > 0 && $bRS) {
            $Inst->pExpire($this->sLockKey, $iExpire);
        }

        return $bRS;
    }

    /**
     * @return bool
     */
    public function release()
    {
        return $this->_getInst()->del($this->sLockKey);
    }


    /** @var \Redis */
    private $_nInst = null;

    /**
     * @param Redis $Redis
     */
    public function _setInst(Redis $Redis)
    {
        $this->_nInst = $Redis;
    }

    /**
     * @return \Redis
     */
    public function _getInst()
    {
        if ($this->_nInst === null) {
            throw new \RuntimeException('[Lock] ; Inst is not set before');
        }

        return $this->_nInst;
    }
}