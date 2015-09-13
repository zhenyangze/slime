<?php
namespace Slime\Component\Support;

class CompatibleEmpty
{
    public function isEmpty()
    {
        return true;
    }

    public function __call($sMethod, $aArg)
    {
        return $this;
    }

    public function __get($sName)
    {
        return null;
    }

    public function __toString()
    {
        return '';
    }
}
