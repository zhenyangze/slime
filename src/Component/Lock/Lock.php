<?php
namespace Slime\Component\Lock;

use Slime\Component\Support\Sugar;

/**
 * Class Lock
 *
 * @package Slime\Component\Lock
 * @author  smallslime@gmail.com
 */
final class Lock
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