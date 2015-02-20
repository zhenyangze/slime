<?php
namespace Slime\Framework;

use Slime\Component\Support\Context;

class ExtHandle
{
    public static function hUncaught(\Exception $E)
    {
        $CTX = Context::inst();
        if (($Log = $CTX->getIgnore('Log')) === null) {
            var_dump($E->getMessage(), $E->getTrace());
            exit(1);
        }

        $Log->error($E->getMessage());

        if ($CTX->isBound('RESP')) {
            $RESP = $CTX->RESP;
            if ($RESP->getStatus() < 400) {
                $RESP->setStatus(500);
            }
            $aArr = $E->getTrace();
            foreach ($aArr as $iK => $aItem) {
                if (isset($aItem['args'])) {
                    unset($aArr[$iK]['args']);
                }
            }
            $RESP->setBody(sprintf(
                '<h1>%s</h1><h2>%d:%s</h2><h3>File:%s;Line:%s</h3><div><pre>%s</pre></div>',
                get_class($E),
                $E->getCode(),
                $E->getMessage(),
                $E->getFile(),
                $E->getLine(),
                var_export($aArr, true)
            ))->send();
        }
        exit(1);
    }

    public static function hException(\Exception $E)
    {
        $CTX = Context::inst();
        if (($Log = $CTX->getIgnore('Log')) === null) {
            var_dump($E->getMessage(), $E->getTrace());
            exit(1);
        }

        $Log->error($E->getMessage());
    }

    public static function hError($iErrNum, $sErrStr, $sErrFile, $iErrLine, $sErrContext)
    {
        $sStr = $iErrNum . ':' . $sErrStr . "\nIn File[$sErrFile]:Line[$iErrLine]";
        if ($iErrNum === E_USER_ERROR) {
            throw new \ErrorException($sStr);
        }
        $CTX  = Context::inst();
        if (($Log = $CTX->getIgnore('Log')) === null) {
            echo $sStr;
            return;
        }

        switch ($iErrNum) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $Log->notice($sStr);
                break;
            default:
                $Log->warning($sStr);
                break;
        }
    }
}