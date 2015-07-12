<?php
namespace Slime\Component\Log;

use Slime\Component\Support\File;

/**
 * Class Writer_File
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Writer_File implements IWriter
{
    protected $aBuf = array();
    protected $iBuf = 0;

    public function __construct(
        $sFileFormat,
        $iBufMax = 0,
        $nsContentFormat = null,
        $aVarMap = null,
        $naLevelMap = null
    ) {
        $this->iBufMax        = $iBufMax;
        $this->sFileFormat    = $sFileFormat;
        $this->sContentFormat = $nsContentFormat === null ? '[{iLevel}] : {sTime} ; {sGuid} ; {sMessage}' : (string)$nsContentFormat;
        $this->aVarMap        = $aVarMap === null ? array('{date}' => date('Y-m-d')) : (array)$aVarMap;
        $this->aLevelMap      = $naLevelMap === null ? array(
            Logger::LEVEL_DEBUG => 'access',
            Logger::LEVEL_INFO  => 'access',
            -1                  => 'error'
        ) : (array)$naLevelMap;
    }

    public function acceptData($aRow)
    {
        $aVarMap = $this->aVarMap;
        if (!isset($aVarMap['{level}'])) {
            $aVarMap['{level}'] = isset($this->aLevelMap[$aRow['iLevel']]) ?
                $this->aLevelMap[$aRow['iLevel']] :
                $this->aLevelMap[-1];
        }
        if (!isset($aVarMap['{date}'])) {
            $aVarMap['{date}'] = date('Y-m-d');
        }
        $sFilePath = str_replace(array_keys($aVarMap), array_values($aVarMap), $this->sFileFormat);

        $sStr = str_replace(
                array('{sTime}', '{iLevel}', '{sMessage}', '{sGuid}'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sContentFormat
            ) . PHP_EOL;

        if ($this->iBufMax > 0) {
            if ($this->iBuf >= $this->iBufMax) {
                $this->_flush();
            } else {
                $this->aBuf[$sFilePath][] = $sStr;
                $this->iBuf++;
            }
        } else {
            if (File::makeFileWithContent($sStr, $sFilePath, FILE_APPEND | LOCK_EX, $sErr) !== 0) {
                trigger_error($sErr, E_USER_WARNING);
            }
        }
    }

    protected function _flush()
    {
        foreach ($this->aBuf as $sFilePath => $aBufData) {
            if (!empty($aBufData)) {
                File::makeFileWithContent($sFilePath, implode('', $aBufData), FILE_APPEND | LOCK_EX);
                $this->aBuf[$sFilePath] = array();
            }
        }
        $this->iBuf = 0;
    }

    public function __destruct()
    {
        $this->_flush();
    }
}
