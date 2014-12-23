<?php
namespace Slime\Bundle\BGJob;

/**
 * Class Daemon
 *
 * @package Slime\Bundle\BGJob
 * @author  smallslime@gmail.com
 */
class Daemon
{
    /**
     * @param IQueue                               $Queue
     * @param \Slime\Component\Log\LoggerInterface $Log same as psr3 LoggerInterface
     * @param int                                  $iMaxJob
     * @param null | string                        $nsPHPBin
     */
    public static function run($Queue, $Log, $iMaxJob = 20, $nsPHPBin = null)
    {
        $sPHPBin      = $nsPHPBin === null ? $_SERVER['_'] : $nsPHPBin;
        $aFD          = array();
        $iCurChildren = null; // for line 68
        while (true) {
            if ($iCurChildren >= $iMaxJob) {
                $Log->info("queue full");
                sleep(1);
                goto NEXT;
            }
            $Log->debug(
                sprintf(
                    "Start new loop[mem:%s memTop:%s], Job count:[%d]",
                    memory_get_usage(true),
                    memory_get_peak_usage(true),
                    $iCurChildren
                )
            );

            # get message
            $sMessage = $Queue->pop();
            if ($sMessage === false) {
                goto NEXT;
            }
            $Log->info("get queue message[{$sMessage}]");

            # run
            $rbFD = proc_open("{$sPHPBin} $sMessage", array(), $aPipe);
            if ($rbFD === false) {
                $Log->warning("run cmd [{$sPHPBin} $sMessage] error");
                goto NEXT;
            }
            $aFD[] = $rbFD;

            # next loop
            NEXT:
            foreach ($aFD as $iK => $rFD) {
                $aArr = proc_get_status($rFD);
                if ($aArr === false) {
                    $Log->warning("get proc[$rFD] status error");
                    continue;
                }
                if ($aArr['running'] === false) {
                    proc_close($rFD);
                    unset($aFD[$iK]);
                }
            }

            if (($iNewNum = count($aFD)) !== $iCurChildren) {
                $iCurChildren = $iNewNum;
                $Log->info("Job running count[$iNewNum]");
            }
            while (($iPID = pcntl_wait($iStatus, WNOHANG | WUNTRACED)) > 0) {
                $Log->info("Get SIGCHLD from child[{$iPID}]");
            }
            usleep(10000);
        }
    }
}
