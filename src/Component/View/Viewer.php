<?php
namespace Slime\Component\View;

use Slime\Component\Support\Sugar;

/**
 * Class View
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
final class Viewer
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
        return Sugar::createObjAdaptor(__CLASS__, func_get_args());
    }
}
