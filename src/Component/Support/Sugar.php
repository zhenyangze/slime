<?php
namespace Slime\Component\Support;

/**
 * Class Sugar
 *
 * @package Slime\Component\Support
 * @author  smallslime@gmail.com
 */
class Sugar
{
    /**
     * @param callable $mTryCB
     * @param array    $aParam
     * @param int      $iMaxTimes
     * @param int      $iSleepMS
     *
     * @return mixed|null
     */
    public static function tryIt($mTryCB, array $aParam = array(), $iMaxTimes = -1, $iSleepMS = 10)
    {
        $i     = 0;
        $mData = null;
        while ($iMaxTimes <= 0 || $i < $iMaxTimes) {
            if (($mData = call_user_func_array($mTryCB, $aParam)) !== null) {
                break;
            }
            if ($iSleepMS > 0) {
                usleep($iSleepMS * 1000);
            }
            ++$i;
        }
        return $mData;
    }


    /**
     * @param string $sClassName
     * @param array  $aArgs
     *
     * @return object
     */
    public static function createObj($sClassName, array $aArgs = array())
    {
        if (empty($aArgs)) {
            return new $sClassName();
        } else {
            $Ref = new \ReflectionClass($sClassName);
            return $Ref->newInstanceArgs($aArgs);
        }
    }

    /**
     * @param string $sNS
     * @param array  $aClassAndArgs
     * @param string $sInterface
     * @param string $sAdaptorClassPre
     *
     * @return object
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function createObjAdaptor(
        $sNS,
        array $aClassAndArgs,
        $sInterface = 'IAdaptor',
        $sAdaptorClassPre = 'Adaptor_'
    ) {
        if (empty($aClassAndArgs)) {
            throw new \InvalidArgumentException("[SUGAR] ; Param error[aClassAndArgs can not be empty]");
        }
        $sClassName = array_shift($aClassAndArgs);
        if ($sClassName[0] === '~') {
            $sClassName = $sNS . '\\' . $sAdaptorClassPre . substr($sClassName, 1);
        }
        $Obj = self::createObj($sClassName, $aClassAndArgs);
        if ($sInterface !== null) {
            $sInterface = $sInterface[0] === '\\' ? substr($sInterface, 1) : "$sNS\\$sInterface";
            if (!$Obj instanceof $sInterface) {
                throw new \UnexpectedValueException("[SUGAR] ; Class[{$sClassName}] must implements [$sInterface]");
            }
        }
        return $Obj;
    }

    public static $aCB_LogTime = array('Slime\\Component\\Support\\Sugar', 'logTime');
    public static function logTime($Obj, $sMethod, $aArg, $Local)
    {
        $Local['__RUN_AT__'] = microtime(true);
    }
}