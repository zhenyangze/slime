<?php
namespace Slime\Component\View;

use Slime\Component\Event\Event;

/**
 * Class Adaptor_PHP
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
class Adaptor_PHP implements IAdaptor
{
    const EV_RENDER_BEFORE = 'slime.component.view.adaptor_php.render_before';
    const EV_RENDER_AFTER = 'slime.component.view.adaptor_php.render_after';

    protected $sBaseDir;
    protected $sTpl;
    protected $aData = array();

    /**
     * @param string|null $sBaseDir
     */
    public function __construct($sBaseDir = null)
    {
        if ($sBaseDir !== null) {
            $this->sBaseDir = $sBaseDir;
        }
    }

    /**
     * @param string $sBaseDir
     *
     * @return $this
     */
    public function setBaseDir($sBaseDir)
    {
        $this->sBaseDir = $sBaseDir;
        return $this;
    }

    /**
     * @param string $sTpl
     *
     * @return $this
     */
    public function setTpl($sTpl)
    {
        $this->sTpl = $sTpl;
        return $this;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     * @param bool   $bOverwrite
     *
     * @return $this
     */
    public function assign($sK, $mV, $bOverwrite = true)
    {
        if ($bOverwrite) {
            $this->aData[$sK] = $mV;
        } elseif (!isset($this->aData[$sK])) {
            $this->aData[$sK] = $mV;
        }

        return $this;
    }

    /**
     * @param array $aKVMap
     * @param bool  $bOverwrite
     *
     * @return $this
     */
    public function assignMulti($aKVMap, $bOverwrite = true)
    {
        if ($bOverwrite) {
            $this->aData = array_merge($this->aData, $aKVMap);
        } else {
            $this->aData = array_merge($aKVMap, $this->aData);
        }

        return $this;
    }

    /**
     * @return void
     */
    public function render()
    {
        echo $this->renderAsResult();
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    public function renderAsResult()
    {
        $sFile = $this->sBaseDir . DIRECTORY_SEPARATOR . $this->sTpl;
        if (!file_exists($sFile)) {
            throw new \RuntimeException("[VIEW] ; Template file[{$sFile}] is not exist");
        }
        $aData = $this->aData;
        $nEV   = $this->_getEvent();
        if ($nEV !== null) {
            $Local = new \ArrayObject(
                array(
                    '__FILE__'     => $sFile,
                    '__BASE_DIR__' => $this->sBaseDir,
                    '__TPL__'      => $this->sTpl,
                    '__DATA__'     => $aData
                )
            );
            $this->_nEV->fire(self::EV_RENDER_BEFORE, array($this, __METHOD__, array(), $Local));
        }

        # for a clean env
        $cbRender = function () use ($aData, $sFile) {
            extract($this->aData);
            ob_start();
            require $sFile;
            $sResult = ob_get_contents();
            ob_end_clean();
            return $sResult;
        };
        $sResult  = $cbRender();

        if ($nEV !== null) {
            $Local['__RESULT__'] = $sResult;
            $this->_nEV->fire(self::EV_RENDER_AFTER, array($this, __METHOD__, array(), $Local));
        }

        return $sResult;
    }

    public function subRender($sTpl, array $aData = array())
    {
        $View = clone $this;
        $View->setTpl($sTpl);
        if (!empty($aData)) {
            $View->assignMulti($aData);
        }
        return $View->renderAsResult(true);
    }

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return $this->sBaseDir;
    }

    /**
     * @return string
     */
    public function getTpl()
    {
        return $this->sTpl;
    }


    /** @var null|Event */
    private $_nEV = null;

    /**
     * @param Event $EV
     */
    public function _setEvent(Event $EV)
    {
        $this->_nEV = $EV;
    }

    /**
     * @return null|Event
     */
    public function _getEvent()
    {
        return $this->_nEV;
    }
}
