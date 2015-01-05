<?php
namespace Slime\Component\Log;

/**
 * Class Writer_STDFD
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 */
class Writer_STDFD implements IWriter
{
    protected $sFormat;

    public function __construct($nsFormat = null)
    {
        $this->sFormat = $nsFormat === null ? '[{iLevel}] : {sTime} ; {sMessage}' : $nsFormat;
    }

    public function acceptData($aRow)
    {
        $sStr = str_replace(
                array('{sTime}', '{iLevel}', '{sMessage}', '{sGuid}'),
                array($aRow['sTime'], Logger::getLevelString($aRow['iLevel']), $aRow['sMessage'], $aRow['sGuid']),
                $this->sFormat
            ) . PHP_EOL;

        if ($aRow['iLevel'] == Logger::LEVEL_DEBUG) {
            fprintf(STDOUT, $sStr, null);
        } else {
            fprintf(STDERR, $sStr, null);
        }
    }
}