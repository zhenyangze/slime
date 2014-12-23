<?php
namespace Slime\Bundle\Framework;

/**
 * Class Controller_Cli
 * Slime 内置Cli控制器基类
 *
 * @package Slime\Bundle\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_CLI extends Controller_ABS
{
    /** @var null|\Slime\Component\Lock\IAdaptor */
    protected $nProcessLock;

    /** @var null|string */
    protected $nsReleaseParam = '__RELEASE_LOCK__';

    /** @var int */
    protected $iLockExpire = 3600;

    protected $bSingle = true;

    public function __before__($sMainAction)
    {
        if (!$this->bSingle) {
            return;
        }

        $this->nProcessLock = $this->CTX->make('Lock', array(2 => __CLASS__ . ":$sMainAction"), true);
        if ($this->nProcessLock === null) {
            return;
        }
        if ($this->nsReleaseParam!==null && $this->getParam($this->nsReleaseParam)) {
            $this->nProcessLock->release();
        }
        if ($this->nProcessLock->acquire($this->iLockExpire, 0) === false) {
            $this->Log->info('[CONTROLLER] ; get lock failed');
            exit();
        } else {
            $this->Log->info('[CONTROLLER] ; get lock successful');
        }
    }

    public function __after__()
    {
        if ($this->nProcessLock !== null) {
            $this->nProcessLock->release();
        }
    }
}