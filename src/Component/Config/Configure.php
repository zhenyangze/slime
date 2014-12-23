<?php
namespace Slime\Component\Config;

use Slime\Component\Support\Sugar;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
final class Configure
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