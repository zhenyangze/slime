<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class Bind
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 *
 * @property-read Bind   $Bind
 * @property-read string $sK
 * @property-read mixed  $mV
 * @property-read mixed  $mAttr
 */
class BindItem
{
    public function __construct($Bind, $sK, $mV, $mAttr = null)
    {
        $this->Bind  = $Bind;
        $this->sK    = $sK;
        $this->mV    = $mV;
        $this->mAttr = $mAttr;
    }

    public function setAttr($mV)
    {
        $this->mAttr = $mV;

        return $this;
    }

    public function __toString()
    {
        return ":$this->sK";
    }

    public function changeV($mV)
    {
        $this->mV = $mV;
    }
}
