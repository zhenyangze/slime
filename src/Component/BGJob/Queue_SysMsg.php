<?php
namespace Slime\Component\BGJob;

/**
 * Class Queue_SysMsg
 *
 * @package Slime\Framework\BGJob
 * @author  smallslime@gmail.com
 */
class Queue_SysMsg implements IQueue
{
    public function __construct($sFilePathForFTOK, $sProjectWithOneChar = 'A')
    {
        $this->MSGQueue = msg_get_queue(ftok($sFilePathForFTOK, $sProjectWithOneChar), 0666);
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
        msg_receive($this->MSGQueue, 1, $iMSGType, 1024, $mRS, true, MSG_IPC_NOWAIT, $iErrorCode);

        if ($iErrorCode != 0) {
            $iErr     = 1;
            $sErr     = sprintf('[BGJob] ; pop form sys_msg failed [%d]', $iErrorCode);
            return false;
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
        $bResult = msg_send($this->MSGQueue, 1, $sJob, true, false, $iErrorCode);
        if (!$bResult) {
            $iErr = 1;
            $sErr = sprintf('Error to push msg[%s] into msgqueue[%d]', $sJob, $iErrorCode);
        } else {
            $iErr = 0;
            $sErr = '';
        }
    }
}
