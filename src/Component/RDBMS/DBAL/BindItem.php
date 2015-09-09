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
 */
class BindItem
{
    public function __construct($Bind, $sK, $mV)
    {
        $this->Bind  = $Bind;
        $this->sK    = $sK;
        $this->mV    = $mV;
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
