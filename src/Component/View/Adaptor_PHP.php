<?php
namespace Slime\Component\View;

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
    protected $nEV = null;

    /**
     * @param string|null                       $sBaseDir
     * @param null|\Slime\Component\Event\Event $nEV
     */
    public function __construct($sBaseDir = null, $nEV = null)
    {
        if ($sBaseDir !== null) {
            $this->sBaseDir = $sBaseDir;
        }
        $this->nEV = $nEV;
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
        if ($this->nEV) {
            $Local = new \ArrayObject(
                array(
                    '__FILE__'     => $sFile,
                    '__BASE_DIR__' => $this->sBaseDir,
                    '__TPL__'      => $this->sTpl,
                    '__DATA__'     => $aData
                )
            );
            $this->nEV->fire(self::EV_RENDER_BEFORE, array($this, __METHOD__, array(), $Local));
        }
        $cbRender = function () use ($aData, $sFile) {
            extract($this->aData);
            ob_start();
            require $sFile;
            $sResult = ob_get_contents();
            ob_end_clean();
            return $sResult;
        };
        $sResult  = $cbRender();

        if ($this->nEV) {
            $this->nEV->fire(self::EV_RENDER_AFTER, array($this, __METHOD__, array(), $Local));
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
}
