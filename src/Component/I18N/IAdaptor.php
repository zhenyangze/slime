<?php
namespace Slime\Component\I18N;

interface IAdaptor
{
    /**
     * @param string $sKey
     *
     * @return string
     */
    public function get($sKey);
}