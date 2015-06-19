<?php
namespace Slime\Component\Support;

class File
{
    public static function autoFormatSize($iByte, $sFormat = '%.2f%s', $naMapFormat = null)
    {
        static $aMap = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $fData = $iByte;
        $i     = 0;
        while (($fTmp = $fData / 1024) >= 1) {
            $fData = $fTmp;
            $i++;
        }

        if ($naMapFormat !== null && isset($naMapFormat[$aMap[$i]])) {
            $sFormat = $naMapFormat[$aMap[$i]];
        }

        return sprintf($sFormat, $fData, isset($aMap[$i]) ? $aMap[$i] : '-');
    }
}