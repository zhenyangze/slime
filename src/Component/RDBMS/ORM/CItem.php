<?php
namespace Slime\Component\RDBMS\ORM;

/**
 * Class CompatibleItem
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class CItem
{
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