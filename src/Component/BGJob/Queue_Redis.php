<?php
namespace Slime\Component\BGJob;

/**
 * Class Queue_Redis
 *
 * @package Slime\Framework\BGJob
 * @author  smallslime@gmail.com
 */
class Queue_Redis implements IQueue
{
    /**
     * @param \Redis $Redis      Redis instance
     * @param string $sQueueName queue name
     */
    public function __construct($Redis, $sQueueName)
    {
        $this->Redis      = $Redis;
        $this->sQueueName = $sQueueName;
    }

    /**
     * Pop an job from queue
     *
     * @param int    $iErr
     * @param string $sErr
     *
     * @return string|bool
     */
    public function pop(&$iErr = 0, &$sErr = '')
    {
        $mRS = $this->Redis->brPop($this->sQueueName);
        if ($mRS === false) {
            $iErr = 1;
            $sErr = 'Pop Failed';
            $mRS  = false;
        }
        return $mRS;
    }

    /**
     * Push an job into queue
     *
     * @param string $sJob
     * @param int    $iErr
     * @param string $sErr
     *
     * @return void
     */
    public function push($sJob, &$iErr = 0, &$sErr = '')
    {
        $bRS = $this->Redis->lPush($this->sQueueName, $sJob);
        if ($bRS === false) {
            $iErr = 1;
            $sErr = 'Push Failed';
        }
    }
}