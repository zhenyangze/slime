<?php
namespace Slime\Component\Config;

/**
 * Class Adaptor_ABS
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
abstract class Adaptor_ABS implements IAdaptor
{
    public function parse($mData, $mDefault, $bForce)
    {
        if (is_string($mData) && strlen($mData) > 1) {
            if ($mData[0] === '@') {
                return $this->get(substr($mData, 1), $mDefault, $bForce);
            } else {
                if ($mData[0] === '\\' && ($mData[1] === '\\' || $mData[1] === '@')) {
                    return substr($mData, 1);
                } else {
                    return $mData;
                }
            }
        } elseif (is_array($mData)) {
            foreach ($mData as $mK => $mV) {
                $mData[$mK] = $this->parse($mV, $mDefault, $bForce);
            }
        }

        return $mData;
    }
}

