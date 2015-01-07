<?php
namespace Slime\Bundle\Framework;

use Slime\Component\Http\REQ;
use Slime\Component\Http\RESP;

/**
 * Class Bootstrap
 *
 * @package Slime\Bundle\Framework
 */
class Bootstrap
{
    const EV_PRE_RUN = 'slime.framework.bootstrap.pre_run';
    const EV_PRE_ROUTE = 'slime.framework.bootstrap.pre_route';
    const EV_HTTP_PRE_SEND = 'slime.framework.bootstrap.http_pre_send';
    const EV_AFTER_RUN = 'slime.framework.bootstrap.after_run';
    const EV_DESTROY = 'slime.framework.bootstrap.destroy';


    /** @var \Slime\Component\Support\Context */
    public $CTX;
    /** @var \Slime\Component\Config\IAdaptor */
    public $CFG;
    /** @var \Slime\Component\Event\Event */
    public $Event;
    /** @var \Slime\Component\Route\Router */
    public $Router;

    /** @var \Slime\Component\Log\LoggerInterface */
    public $Log;
    /** @var string */
    public $sAPI;

    protected $aParamForEV = array();

    public function __construct(InitBean $InitBean)
    {
        $this->InitBean    = $InitBean;
        $this->CTX         = $InitBean->CTX;
        $this->CFG         = $InitBean->CFG;
        $this->Event       = $InitBean->Event;
        $this->Router      = $InitBean->Router;
        $this->aParamForEV = array($this, new \ArrayObject());
    }

    public function run($nsSAPI = null)
    {
        # register
        $this->sAPI = $nsSAPI === null ? PHP_SAPI : $nsSAPI;
        if ($this->InitBean->mHErr !== null) {
            set_error_handler($this->InitBean->mHErr, $this->InitBean->iHErr);
        }
        if ($this->InitBean->mHException !== null) {
            set_exception_handler($this->InitBean->mHException);
        }
        $this->Log = $this->InitBean->getLog();
        if ($this->InitBean->nsBootstrapKey !== null) {
            $this->CTX->bind($this->InitBean->nsBootstrapKey, $this);
        }

        # run
        $this->Event->fire(self::EV_PRE_RUN, $this->aParamForEV);
        try {
            $this->sAPI === 'cli' ? $this->runCli() : $this->runHttp();
        } catch (\Exception $E) {
            if ($this->InitBean->mHUnCaught !== null) {
                call_user_func($this->InitBean->mHUnCaught, $E);
            }
            exit(1);
        }
        $this->Event->fire(self::EV_AFTER_RUN, $this->aParamForEV);
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
        $this->Event->fire(self::EV_DESTROY, $this->aParamForEV);
    }
}
