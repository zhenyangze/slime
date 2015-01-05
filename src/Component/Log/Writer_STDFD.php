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

        //@todo  if str has %s %d etc .. fprintf will show arg error
        if ($aRow['iLevel'] <= Logger::LEVEL_INFO) {
            fprintf(STDOUT, $sStr);
        } else {
            fprintf(STDERR, $sStr);
        }
    }
}