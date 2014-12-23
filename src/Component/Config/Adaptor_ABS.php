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
    protected $nCTX;

    /**
     * @param \Slime\Component\Support\Context $CTX
     *
     * @return void
     */
    public function setCTX($CTX)
    {
        $this->nCTX = $CTX;
    }

    public function parse($mData, $bForce)
    {
        if (is_string($mData) && strlen($mData) > 1) {
            $sChr = $mData[0];
            $sFix = $mData;
            if ($mData[0] === '@' || $mData[0] === ':') {
                $sFix = ltrim($mData, $sChr);
            }
            $iDiff = strlen($mData) - strlen($sFix);
            $sFix  = str_repeat($sChr, (int)($iDiff / 2)) . $sFix;
            if ($iDiff % 2 !== 0) {
                if ($sChr === ':') {
                    $mData = $this->nCTX->$sFix;
                } else {
                    $mData = $bForce ? $this->getForce($sFix) : $this->get($sFix);
                }
            } else {
                $mData = $sFix;
            }
        } elseif (is_array($mData)) {
            foreach ($mData as $mK => $mV) {
                $mData[$mK] = $this->parse($mV, $bForce);
            }
        }

        return $mData;
    }


    /**
     * @param string $sKey
     * @param bool   $bWithParse
     *
     * @return mixed
     */
    public function getForce($sKey, $bWithParse = false)
    {
        if (($mResult = $this->get($sKey, null, false)) === false) {
            throw new \OutOfBoundsException("[CONFIG] ; Key[$sKey] is not found");
        }

        if ($bWithParse) {
            $mResult = $this->parse($mResult, true);
        }
        return $mResult;
    }
}

