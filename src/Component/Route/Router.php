<?php
namespace Slime\Component\Route;

/**
 * Class Route
 *
 * @package Slime\Component\Route
 * @author  smallslime@gmail.com
 */
class Router
{
    protected $aConfig = array();

    /**
     * @param array $aConfig
     *
     * @return $this
     */
    public function addConfig(array $aConfig)
    {
        $this->aConfig = array_merge($this->aConfig, $aConfig);

        return $this;
    }

    /**
     * @param string $sRE
     * @param mixed  $mCB
     *
     * @return $this
     */
    public function addGET($sRE, $mCB)
    {
        $this->aConfig[] = array('__FILTERS__' => array('@isGET'), '__RE__' => $sRE, '__CB__' => $mCB);

        return $this;
    }

    /**
     * @param string $sRE
     * @param mixed  $mCB
     *
     * @return $this
     */
    public function addPOST($sRE, $mCB)
    {
        $this->aConfig[] = array('__FILTERS__' => array('@isPOST'), '__RE__' => $sRE, '__CB__' => $mCB);

        return $this;
    }

    /**
     * @param   string   $sRE
     * @param   mixed    $mCB
     * @param null|array $naFilter
     *
     * @return $this
     */
    public function add($sRE, $mCB, $naFilter = null)
    {
        $this->aConfig[] = array('__RE__' => $sRE, '__CB__' => $mCB, '__FILTERS__' => $naFilter);

        return $this;
    }

    /**
     * @param \Slime\Component\Http\REQ        $REQ
     * @param \Slime\Component\Http\RESP       $RESP
     * @param \Slime\Component\Support\Context $CTX
     */
    public function runHttp($REQ, $RESP, $CTX)
    {
        $aDefaultParam = array($REQ, $RESP, $CTX);
        $sUrl          = $REQ->getUrl();
        $bHit          = false;
        foreach ($this->aConfig as $aArr) {
            # filters
            if (isset($aArr['__FILTERS__'])) {
                foreach ($aArr['__FILTERS__'] as $aItem) {
                    if (!is_array($aItem) || empty($aItem)) {
                        trigger_error('[ROUTE] ; Every filter must be an array[cb, param1, ...]', E_USER_WARNING);
                        continue;
                    }
                    $mCB = array_shift($aItem);
                    if (is_string($mCB) && $mCB[0] === '@') {
                        $mCB = array('\\Slime\\Component\\Route\\Filter', substr($mCB, 1));
                    }
                    if (!call_user_func_array($mCB, array_merge($aDefaultParam, $aItem))) {
                        continue 2;
                    }
                }
            }

            # match
            $aParam = $aDefaultParam;
            if (isset($aArr['__RE__'])) {
                if (!preg_match($aArr['__RE__'], $sUrl, $aMatch)) {
                    continue;
                }
                array_shift($aMatch);
                $aParam = array_merge($aParam, $aMatch);
            }
            if (isset($aArr['__PARAM__'])) {
                $aParam[] = $aArr['__PARAM__'];
            }
            if (!$bHit) {
                $bHit = true;
            }
            if (!call_user_func_array($aArr['__CB__'], $aParam)) {
                break;
            }
        }

        if (!$bHit && $RESP->getStatus() === null) {
            $RESP->setStatus(404)->setBody('None routes hit!');
        }
    }

    public function runCli($aArgv, $CTX)
    {
        foreach ($this->aConfig as $aArr) {
            if (!call_user_func($aArr['__CB__'],
                $aArgv, $CTX,
                isset($aArr['__PARAM__']) ? $aArr['__PARAM__'] : array())
            ) {
                break;
            }
        }
    }
}

