<?php
namespace Slime\Framework;

use Slime\Component\Config\IAdaptor as Config_IAdaptor;
use Slime\Component\Event\Event;
use Slime\Component\Log\LoggerInterface;
use Slime\Component\Route\Router;
use Slime\Component\Support\Context;
use Slime\Component\Support\Sugar;

class InitBean
{
    /** @var \Slime\Component\Config\IAdaptor */
    public $CFG;
    public $nsCFGKey;

    public $aCTXData;

    /** @var \Slime\Component\Event\Event */
    public $Event;
    public $nsEventKey;

    /** @var \Slime\Component\Route\Router */
    public $Router;
    public $nsRouterKey;

    public $iHErr = E_ALL;
    public $mHErr = array('Slime\\Bundle\\Framework\\ExtHandle', 'hError');
    public $mHException = array('Slime\\Bundle\\Framework\\ExtHandle', 'hException');
    public $mHUnCaught = array('Slime\\Bundle\\Framework\\ExtHandle', 'hUncaught');

    public $nsBootstrapKey = '__BOOTSTRAP__';

    public static function factory()
    {
        return new self();
    }

    public function set_1_ErrHandle($mHErr)
    {
        $this->mHErr = $mHErr;
        return $this;
    }

    public function set_1_ErrHandleLevel($iLevel)
    {
        $this->iHErr = $iLevel;
        return $this;
    }

    public function set_1_ExceptionHandle($mHException)
    {
        $this->mHException = $mHException;
        return $this;
    }

    public function set_1_UnCaughtHandle($mHUnCaught)
    {
        $this->mHUnCaught = $mHUnCaught;
        return $this;
    }


    public function set_1_CFG($m_n_aConf_sInitConfKey = null, $CFG = null, $nsCTXKey = 'Config')
    {
        if ($CFG !== null) {
            if (!$CFG instanceof Config_IAdaptor) {
                throw new \RuntimeException("[MAIN] ; Event instance error");
            }
            $this->CFG = $CFG;
        } else {
            # create and init
            $this->CFG = Sugar::createObjAdaptor('Slime\\Component\\Config', $m_n_aConf_sInitConfKey);
        }

        $this->nsCFGKey = $nsCTXKey;

        return $this;
    }


    public function set_2_CompConf($m_sKForConf_aConf)
    {
        if (is_array($m_sKForConf_aConf)) {
            $this->aCTXData = (array)$m_sKForConf_aConf;
        } elseif (is_string($m_sKForConf_aConf)) {
            if ($this->CFG === null) {
                throw new \RuntimeException('[BOOTSTRAP_INIT] ; Config is not set before');
            }
            $aCTXData = array();
            foreach (explode(';', $m_sKForConf_aConf) as $sK) {
                $aCTXData = array_merge($aCTXData, $this->CFG->get($sK));
            }
            $this->aCTXData = $aCTXData;
        }
        return $this;
    }


    public function set_2_Event($m_n_aConf_sInitConfKey = null, $Event = null, $nsCTXKey = 'Event')
    {
        if ($Event !== null) {
            if (!$Event instanceof Event) {
                throw new \RuntimeException("[MAIN] ; Event instance error");
            }
            $this->Event = $Event;
        } else {
            $this->Event = new Event();
        }
        $this->nsEventKey = $nsCTXKey;

        # init
        if ($m_n_aConf_sInitConfKey !== null) {
            $aConf = is_string($m_n_aConf_sInitConfKey) ?
                $this->CFG->get($m_n_aConf_sInitConfKey) :
                $m_n_aConf_sInitConfKey;
            foreach ($aConf as $sK => $aGroup) {
                foreach ($aGroup as $aRow) {
                    if (isset($aRow['__CB__'])) {
                        $this->Event->listen(
                            $sK,
                            $aRow['__CB__'],
                            isset($aRow['__PRI__']) ? (int)$aRow['__PRI__'] : 0,
                            isset($aRow['__SIGN__']) ? (string)$aRow['__SIGN__'] : null,
                            isset($aRow['__ENV_VAR__']) ? (array)$aRow['__ENV_VAR__'] : array()
                        );
                    } else {
                        $this->Event->listen($sK, $aRow);
                    }
                }
            }
        }

        return $this;
    }

    public function set_2_Router($m_n_aConf_sInitConfKey = null, $Router = null, $nsCTXKey = 'Router')
    {
        if ($Router !== null) {
            if (!$Router instanceof Router) {
                throw new \RuntimeException("[MAIN] ; Event instance error");
            }
            $this->Router = $Router;
        } else {
            $this->Router = new Router();
        }

        # init
        $this->nsRouterKey = $nsCTXKey;
        if ($m_n_aConf_sInitConfKey !== null) {
            $this->Router->addConfig(
                is_string($m_n_aConf_sInitConfKey) ?
                    $this->CFG->get($m_n_aConf_sInitConfKey) :
                    $m_n_aConf_sInitConfKey
            );
        }

        return $this;
    }

    /******** run each req *******/

    public static $sKeyOfCTXInGlobal = '__MAIN_CONTEXT__';

    /** @var Context */
    public $CTX;

    public $m_Log_sLogKeyInComp = 'Log';

    /** @var \Slime\Component\Log\Logger */
    protected $Log;

    public function buildContext()
    {
        $this->CTX = new Context($this->aCTXData);
        $aMap      = array();
        if ($this->nsCFGKey !== null) {
            $aMap[$this->nsCFGKey] = $this->CFG;
        }
        if ($this->nsEventKey !== null) {
            $aMap[$this->nsEventKey] = $this->Event;
        }
        if ($this->nsRouterKey !== null) {
            $aMap[$this->nsRouterKey] = $this->Router;
        }
        if (!empty($aMap)) {
            $this->CTX->bindMulti($aMap);
        }

        # set in global
        $GLOBALS[self::$sKeyOfCTXInGlobal] = $this->CTX;

        return $this;
    }

    public function setLog($m_Log_sLogKeyInComp)
    {
        $this->m_Log_sLogKeyInComp = $m_Log_sLogKeyInComp;
    }

    public function getLog()
    {
        if ($this->Log === null) {
            $this->Log = is_string($this->m_Log_sLogKeyInComp) ?
                $this->CTX->{$this->m_Log_sLogKeyInComp} :
                $this->m_Log_sLogKeyInComp;
            if (!$this->Log instanceof LoggerInterface) {
                throw new \RuntimeException("[MAIN] ; Log instance error");
            }
        }

        return $this->Log;
    }
}