<?php
namespace Slime\Component\Log;

use Slime\Component\Support\Sugar;

/**
 * Class Logger
 *
 * @package Slime\Component\Log
 * @author  smallslime@gmail.com
 *
 * @property-read IWriter[] $aWriter
 * @property-read int       $iLogLevel
 * @property-read string    $sGUID
 */
class Logger implements LoggerInterface
{
    const DESC_EMERGENCY = 'emergency';
    const DESC_ALERT = 'alert';
    const DESC_CRITICAL = 'critical';
    const DESC_ERROR = 'error';
    const DESC_WARNING = 'warning';
    const DESC_NOTICE = 'notice';
    const DESC_INFO = 'info';
    const DESC_DEBUG = 'debug';

    const LEVEL_ALL = 255;
    const LEVEL_EMERGENCY = 128;
    const LEVEL_ALERT = 64;
    const LEVEL_CRITICAL = 32;
    const LEVEL_ERROR = 16;
    const LEVEL_WARNING = 8;
    const LEVEL_NOTICE = 4;
    const LEVEL_INFO = 2;
    const LEVEL_DEBUG = 1;

    public static $aMap = array(
        self::LEVEL_EMERGENCY => 'emergency',
        self::LEVEL_ALERT     => 'alert',
        self::LEVEL_CRITICAL  => 'critical',
        self::LEVEL_ERROR     => 'error',
        self::LEVEL_WARNING   => 'warning',
        self::LEVEL_NOTICE    => 'notice',
        self::LEVEL_INFO      => 'info',
        self::LEVEL_DEBUG     => 'debug'
    );

    protected $niLimit = null;

    /**
     * @param array    $aWriterConf ['File' => ['@File', 'param1', 'param2'], '@FirePHP']
     * @param int      $iLogLevel
     * @param null     $sRequestID
     * @param null|int $niLimit
     */
    public function __construct(
        array $aWriterConf,
        $iLogLevel = self::LEVEL_ALL,
        $sRequestID = null,
        $niLimit = null
    ) {
        foreach ($aWriterConf as $sK => $aClassAndArgs) {
            $this->aWriter[$sK] = Sugar::createObjAdaptor(__NAMESPACE__, $aClassAndArgs, 'IWriter', 'Writer_');
        }
        $this->iLogLevel = $iLogLevel;
        $this->sGUID     = base_convert(rand(10, 99) . str_replace('.', '', round(microtime(true), 4)), 10, 32);

        // limit:5   message:abcdefg  result:ab...
        if (is_int($niLimit) && ($niLimit > 3)) {
            $this->niLimit = $niLimit - 3;
        }
    }

    /**
     * @param string       $sK
     * @param null | array $aClassAndArgs
     * @param bool         $bOnlyAdd
     */
    public function setWriter($sK, $aClassAndArgs = null, $bOnlyAdd = false)
    {
        if ($aClassAndArgs === null) {
            if (isset($this->aWriter[$sK])) {
                unset($this->aWriter[$sK]);
            }
        } else {
            if (!$bOnlyAdd || !isset($this->aWriter[$sK])) {
                $this->aWriter[$sK] = Sugar::createObjAdaptor(__NAMESPACE__, $aClassAndArgs, 'IWriter', 'Writer_');;
            }
        }
    }

    /**
     * @param null|int $niLimit
     */
    public function setLimit($niLimit)
    {
        $this->niLimit = $niLimit;
    }

    /**
     * @param int $iLevel
     *
     * @return bool
     */
    public function isNeed($iLevel)
    {
        return (bool)$this->iLogLevel|$iLevel;
    }

    /**
     * System is unusable.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function emergency($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_EMERGENCY, $sMessage, $aContext);
    }

    /**
     * Action must be taken immediately.
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function alert($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_ALERT, $sMessage, $aContext);
    }

    /**
     * Critical conditions.
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function critical($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_CRITICAL, $sMessage, $aContext);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function error($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_ERROR, $sMessage, $aContext);
    }

    /**
     * Exceptional occurrences that are not errors.
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function warning($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_WARNING, $sMessage, $aContext);
    }

    /**
     * Normal but significant events.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function notice($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_NOTICE, $sMessage, $aContext);
    }

    /**
     * Interesting events.
     * Example: User logs in, SQL logs.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function info($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_INFO, $sMessage, $aContext);
    }

    /**
     * Detailed debug information.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return null
     */
    public function debug($sMessage, array $aContext = array())
    {
        $this->log(self::LEVEL_DEBUG, $sMessage, $aContext);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param int    $iLevel
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return void
     */
    public function log($iLevel, $sMessage, array $aContext = array())
    {
        if (!($iLevel & $this->iLogLevel) || empty($this->aWriter)) {
            return;
        }

        $sMessage = self::interpolate($sMessage, $aContext);
        if (is_int($this->niLimit) && strlen($sMessage) > $this->niLimit) {
            $sMessage = substr($sMessage, 0, $this->niLimit) . '...';
        }

        list($sUSec, $sSec) = explode(' ', microtime());
        $sTime = date('Y-m-d H:i:s', $sSec) . '.' . substr($sUSec, 2, 4);

        $aRow = array('sTime' => $sTime, 'iLevel' => $iLevel, 'sMessage' => $sMessage, 'sGuid' => $this->sGUID);
        foreach ($this->aWriter as $Writer) {
            $Writer->acceptData($aRow);
        }
    }

    public static function getLevelString($iLevel)
    {
        return isset(self::$aMap[$iLevel]) ? self::$aMap[$iLevel] : 'unknown';
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string $sMessage
     * @param array  $aContext
     *
     * @return string
     */
    public static function interpolate($sMessage, array $aContext = array())
    {
        // build a replacement array with braces around the context keys
        $aReplace = array();
        foreach ($aContext as $sK => $mV) {
            $aReplace['{' . $sK . '}'] = (is_array($mV) || is_object($mV)) ? json_encode($mV) : (string)$mV;
        }

        // interpolate replacement values into the message and return
        return strtr($sMessage, $aReplace);
    }
}
