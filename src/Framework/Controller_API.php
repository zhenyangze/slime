<?php
namespace Slime\Framework;

use Slime\Component\Http;
use Slime\Component\Support\XML;
use Slime\Component\View;

/**
 * Class Controller_API
 * Slime 内置Http控制器基类
 *
 * @package Slime\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_API extends Controller_ABS
{
    protected $sDefaultRender = '_renderJSON';
    protected $sJSCBParam = 'cb';
    protected $nsXmlTPL = null;
    protected $nsJsonTPL = null;
    protected $nsJsonPTPL = null;

    protected $aData = array();

    /** @var \Slime\Component\Http\REQ */
    protected $REQ;
    /** @var \Slime\Component\Http\RESP */
    protected $RESP;

    public function __construct($CTX, array $aParam = array())
    {
        parent::__construct($CTX, $aParam);
        $this->REQ  = $this->CTX->REQ;
        $this->RESP = $this->CTX->RESP;
    }

    protected function success(array $aData = array())
    {
        $this->aData['data']    = $aData;
        $this->aData['errCode'] = 0;
        $this->aData['errMsg']  = '';
    }

    protected function fail($sErr, $iErr = 1, array $aData = array())
    {
        $this->aData['data']    = $aData;
        $this->aData['errCode'] = $iErr;
        $this->aData['errMsg']  = $sErr;
    }

    public function __after__()
    {
        if (empty($this->aParam['__EXT__'])) {
            $sMethodName = $this->sDefaultRender;
        } else {
            $sMethodName = '_render' . strtoupper($this->aParam['__EXT__']);
            if ($this->sDefaultRender !== null && !method_exists($this, $sMethodName)) {
                $sMethodName = $this->sDefaultRender;
            }
        }

        $this->$sMethodName();
    }

    protected function _renderXML()
    {
        $this->RESP
            ->setHeader('Content-Type', 'text/xml; charset=utf-8', false)
            ->setBody(
                $this->nsXmlTPL === null ?
                    XML::Array2XML($this->aData) :
                    $this->CTX->View->assignMulti($this->aData)->setTpl($this->nsXmlTPL)->renderAsResult()
            );
    }

    protected function _renderJSON()
    {
        $this->RESP
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8', false)
            ->setBody(
                $this->nsJsonTPL === null ?
                    json_encode($this->aData) :
                    $this->CTX->View->assignMulti($this->aData)->setTpl($this->nsJsonTPL)->renderAsResult()
            );
    }

    protected function _renderJSONP()
    {
        $sCB = $this->REQ->getG($this->sJSCBParam);
        if ($sCB === null) {
            $sCB = 'cb';
        }
        $this->RESP
            ->setHeader('Content-Type', 'text/javascript; charset=utf-8', false)
            ->setBody(
                $this->nsJsonPTPL === null ?
                    $sCB . '(' . json_encode($this->aData) . ')' :
                    $this->CTX->View->assignMulti($this->aData)->setTpl($this->nsJsonPTPL)->renderAsResult()
            );
    }

    protected function _renderJS()
    {
        $this->_renderJSONP();
    }
}