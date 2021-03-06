<?php
namespace Slime\Component\Support;

class File
{
    public static function sizeFormat($iByte, $sFormat = '%.2f%s', $naMapFormat = null)
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


    /**
     * @param string $sFilePath
     * @param int    $niMode
     * @param string $sErr
     *
     * @return int
     */
    public static function makeFile($sFilePath, $niMode = 0777, &$sErr = '')
    {
        if (file_exists($sFilePath)) {
            return 0;
        }

        if ($niMode === null) {
            $niMode = 0777;
        }

        # file_path is dir : create dir
        if (substr($sFilePath, -1) === DIRECTORY_SEPARATOR) {
            if (!mkdir($sFilePath, $niMode, true)) {
                $sErr = "[File] ; create dir[$sFilePath] failed";
                return 1;
            }
            return 0;
        }

        # file_path is relative path file : create file
        if (($iPos = strrpos($sFilePath, DIRECTORY_SEPARATOR)) === false) {
            if (!touch($sFilePath)) {
                $sErr = "[File] ; create file[$sFilePath] failed";
                return 2;
            }
            return 0;
        }

        # file_path is file : find dir and create dir ; find file and create file
        $sDir = substr($sFilePath, 0, $iPos);
        if (!file_exists($sDir) && !mkdir($sDir, $niMode, true)) {
            $sErr = "[File] ; create dir[$sDir] failed";
            return 1;
        }
        if (!touch($sFilePath)) {
            $sErr = "[File] ; create file[$sFilePath] from[$sDir] failed";
            return 2;
        }

        return 0;
    }

    /**
     * @param string $sFileContent
     * @param string $sFilePath
     * @param int    $niMode
     * @param string $sErr
     *
     * @return int
     */
    public static function makeFileWithContent($sFileContent, $sFilePath, $niMode = null, &$sErr = '')
    {
        if (($iErr = File::makeFile($sFilePath, $niMode, $sErr)) > 0) {
            return $iErr;
        }
        if (file_put_contents($sFilePath, $sFileContent, $niMode) === false) {
            $sErr = "[File] ; write [$sFilePath] failed";
            return 3;
        }
        return 0;
    }
}
