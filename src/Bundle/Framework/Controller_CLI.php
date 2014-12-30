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

        /** @var \Slime\Component\Lock\IAdaptor $Lock */
        $Lock = $this->CTX->make('Lock', array(2 => __CLASS__ . ":$sMainAction"), true);
        if ($Lock === null) {
            return;
        }
        $this->CTX->Event->listen(Bootstrap::EV_DESTROY, function(Bootstrap $B, $Env) use($Lock) {
            $Lock->release();
        });
        if ($this->nsReleaseParam!==null && $this->getParam($this->nsReleaseParam)) {
            $Lock->release();
        }
        if ($Lock->acquire($this->iLockExpire, 0) === false) {
            $this->Log->info('[CONTROLLER] ; get lock failed');
            exit();
        } else {
            $this->Log->info('[CONTROLLER] ; get lock successful');
        }
    }
}