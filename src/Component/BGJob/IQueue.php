<?php
namespace Slime\Component\BGJob;

/**
 * Interface IQueue
 *
 * @package Slime\Component\MultiProcessJob
 * @author  smallslime@gmail.com
 */
interface IQueue
{
    /**
     * Pop an job from queue
     *
     * @param int    $iErr
     * @param string $sErr
     *
     * @return mixed
     */
    public function pop(&$iErr = 0, &$sErr = '');

    /**
     * Push an job into queue
     *
     * @param string $sJob
     * @param int    $iErr
     * @param string $sErr
     *
     * @return bool
     */
    public function push($sJob, &$iErr = 0, &$sErr = '');
}