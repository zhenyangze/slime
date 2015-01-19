<?php
namespace Slime\Component\I18N;

use Slime\Component\Support\Sugar;

/**
 * Class I18N
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
class I18N
{
    /**
     * @param string $sAdaptor
     *
     * @return IAdaptor
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function factory($sAdaptor)
    {
        return Sugar::createObjAdaptor(__NAMESPACE__, func_get_args());
    }
}