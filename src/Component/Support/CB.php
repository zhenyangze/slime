<?php
namespace Slime\Component\Support;

class CB
{
    public static function createFromFunc($sFunc)
    {
        return new self($sFunc);
    }

    public static function createFromStatic($sClassName, $sMethodName)
    {
        return new self(array($sClassName, $sMethodName));
    }

    public static function createFromObj($Obj, $sMethodName)
    {
        return new self(array($Obj, $sMethodName));
    }

    public function __construct($mCB, $naEnvVar = null)
    {
        $this->mCB = $mCB;
        $this->naEnvVar = $naEnvVar;
    }

    public function call()
    {
        return call_user_func_array(
            $this->mCB,
            empty($this->naEnvVar) ? func_get_args() : array_merge(func_get_args(), $this->naEnvVar)
        );
    }
}
