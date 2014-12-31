<?php
namespace Slime\Component\MultiJob;

class MultiJob
{
    /**
     * @param null|mixed $mCBMainInit
     * @param null|mixed $mCBGetLocalVar
     * @param mixed      $mCBChild
     * @param int        $iNumWorker
     * @param int        $iMaxNumChild
     */
    public function __construct(
        $mCBMainInit,
        $mCBGetLocalVar,
        $mCBChild,
        $iNumWorker = 5,
        $iMaxNumChild = 20
    ) {
        $this->mCBMainInit    = $mCBMainInit;
        $this->mCBGetLocalVar = $mCBGetLocalVar;
        $this->mCBChild       = $mCBChild;
        $this->iNumWorker     = $iNumWorker;
        $this->iMaxNumChild   = $iMaxNumChild;
    }

    public function run()
    {
        if ($this->mCBMainInit !== null) {
            call_user_func($this->mCBMainInit, $this->iNumWorker);
        }

        for ($iCurChild = 0; $iCurChild < $this->iNumWorker; $iCurChild++) {
            $iPID = pcntl_fork();
            if ($iPID == -1) {
                $iCurChild--;
                trigger_error("[MultiJob] ; fork process error", E_USER_ERROR);
                continue;
            }

            # child
            if ($iPID == 0) {
                if ($this->mCBGetLocalVar !== null) {
                    call_user_func($this->mCBChild, call_user_func($this->mCBGetLocalVar, $iCurChild, $iPID));
                } else {
                    call_user_func($this->mCBChild);
                }
                exit();
            }

            # father
            // max process controller
            if ($iCurChild >= $this->iMaxNumChild) {
                if (pcntl_wait($iStatus)) {
                    $iCurChild--;
                }
            }
        }

        while ($iCurChild > 0) {
            if (pcntl_wait($iStatus)) {
                $iCurChild--;
            }
        }
    }
}

