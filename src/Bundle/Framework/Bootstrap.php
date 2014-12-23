<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Event\Event;
use Slime\Component\Http\REQ;
use Slime\Component\Http\RESP;
use Slime\Component\Route\Router;
use Slime\Component\Support\Context;

/**
 * Class Bootstrap
 *
 * @package Slime\Bundle\Framework
 */
class Bootstrap
{
    public static function factory(
        $aCFGParam,
        $sContextGFGKey,
        $nsRouterInitKey = null,
        $nsEventInitKey = null,
        $iHErr = E_ALL,
        $mHErr = array('\\Slime\\Bundle\\Framework\\ExtHandle', 'hError'),
        $mHException = array('\\Slime\\Bundle\\Framework\\ExtHandle', 'hException'),
        $mHUnCaught = array('\\Slime\\Bundle\\Framework\\ExtHandle', 'hUncaught'),
        $sLogKeyInCFG = 'Log',
        $sCTX_This = '__BOOTSTRAP__',
        $sCTX_Log = 'Log',
        $sCTX_Config = 'Config',
        $sCTX_Route = 'Router',
        $sCTX_Event = 'Event'
    ) {
        /** @var \Slime\Component\Config\IAdaptor $CFG */
        $CFG = call_user_func_array(array('\Slime\Component\Config\Configure', 'factory'), $aCFGParam);

        $Router = new Router();
        if ($nsRouterInitKey !== null) {
            $Router->addConfig($CFG->getForce($nsRouterInitKey));
        }
        $Event = new Event();
        if ($nsEventInitKey !== null) {
            foreach ($CFG->getForce($nsEventInitKey) as $sKey => $aCB) {
                foreach ($aCB as $iPriority => $mCB) {
                    $Event->listen($sKey, $mCB, $iPriority);
                }
            }
        }
        return new self(
            $sContextGFGKey, $sLogKeyInCFG,
            $CFG, $Router, $Event,
            $sCTX_This, $sCTX_Log, $sCTX_Config, $sCTX_Route, $sCTX_Event, $iHErr,
            $mHErr, $mHException, $mHUnCaught
        );
    }

    const EV_PRE_RUN = 'slime.framework.bootstrap.pre_run';
    const EV_AFTER_RUN = 'slime.framework.bootstrap.after_run';
    const EV_PRE_ROUTE = 'slime.framework.bootstrap.pre_route';
    const EV_HTTP_PRE_SEND = 'slime.framework.bootstrap.http_pre_send';

    /** @var \Slime\Component\Support\Context */
    public $CTX;
    /** @var \Slime\Component\Log\Logger|null */
    public $Log;
    /** @var string */
    protected $sCTX_Log;
    /** @var string */
    protected $sCTX_This;
    /** @var array */
    protected $aParamForEV;

    /**
     * @param string                           $sContextGFGKey
     * @param string                           $sLogKeyInCFG
     * @param \Slime\Component\Config\IAdaptor $CFG
     * @param \Slime\Component\Route\Router    $Router
     * @param \Slime\Component\Event\Event     $Event
     * @param string                           $sCTX_This
     * @param string                           $sCTX_Log
     * @param string                           $sCTX_Config
     * @param string                           $sCTX_Route
     * @param string                           $sCTX_Event
     * @param int                              $iHErr
     * @param mixed                            $mHErr
     * @param mixed                            $mHException
     * @param mixed                            $mHUnCaught
     */
    private function __construct(
        $sContextGFGKey,
        $sLogKeyInCFG,
        $CFG,
        $Router,
        $Event,
        $sCTX_This,
        $sCTX_Log,
        $sCTX_Config,
        $sCTX_Route,
        $sCTX_Event,
        $iHErr,
        $mHErr,
        $mHException,
        $mHUnCaught
    ) {
        $this->sContextGFGKey = $sContextGFGKey;
        $this->sLogKeyInCFG   = $sLogKeyInCFG;
        $this->sCTX_Log       = $sCTX_Log;
        $this->sCTX_This      = $sCTX_This;
        $this->CFG            = $CFG;
        $this->Router         = $Router;
        $this->Event          = $Event;
        $this->mHErr          = $mHErr;
        $this->iHErr          = $iHErr;
        $this->mHException    = $mHException;
        $this->mHUnCaught     = $mHUnCaught;
        $this->aCBMap         = array(
            $sCTX_Config => $CFG,
            $sCTX_Route  => $Router,
            $sCTX_Event  => $Event,
        );
    }

    public function run($nsSAPI = null)
    {
        # register
        $this->aParamForEV = array($this, new \ArrayObject());

        if ($this->mHErr !== null) {
            set_error_handler($this->mHErr, $this->iHErr);
        }
        if ($this->mHException !== null) {
            set_exception_handler($this->mHException);
        }

        # context register
        $this->CTX = Context::create($this->CFG, $this->sContextGFGKey)->bindMulti($this->aCBMap);
        # log init
        $this->Log = $this->CTX->{$this->sLogKeyInCFG};
        $this->CTX->bindMulti(array($this->sCTX_Log => $this->Log, $this->sCTX_This => $this));

        # run
        $this->Event->fire(self::EV_PRE_RUN, $this->aParamForEV);
        try {
            ($nsSAPI === null ? PHP_SAPI : $nsSAPI) === 'cli' ?
                $this->runCli() :
                $this->runHttp();
        } catch (\Exception $E) {
            if ($this->mHUnCaught !== null) {
                call_user_func($this->mHUnCaught, $E);
            }
            exit(1);
        }
    }

    protected function runCli()
    {
        # build argv
        $this->CTX->bind('aArgv', $GLOBALS['argv']);

        # route & run
        $this->Event->fire(self::EV_PRE_ROUTE, $this->aParamForEV);
        $this->Router->runCli($GLOBALS['argv'], $this->CTX);
    }

    protected function runHttp()
    {
        # build req & resp
        $REQ  = REQ::createFromGlobal();
        $RESP = new RESP($REQ->getProtocol());
        $this->CTX->bindMulti(array('REQ' => $REQ, 'RESP' => $RESP));

        # route & run
        $this->Event->fire(self::EV_PRE_ROUTE, $this->aParamForEV);
        $this->Router->runHttp($REQ, $RESP, $this->CTX);

        # send
        $this->Event->fire(self::EV_HTTP_PRE_SEND, $this->aParamForEV);
        $RESP->send();
    }

    public function __destruct()
    {
        $this->Event->fire(self::EV_AFTER_RUN, $this->aParamForEV);
    }
}
