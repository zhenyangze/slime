<?php
namespace Slime\Component\View;

/**
 * Interface IAdaptor
 *
 * @package Slime\Component\View
 * @author  smallslime@gmail.com
 */
interface IAdaptor
{
    /**
     * @param string $sBaseDir
     *
     * @return $this
     */
    public function setBaseDir($sBaseDir);

    /**
     * @return string
     */
    public function getBaseDir();

    /**
     * @param string $sTpl
     *
     * @return IAdaptor
     */
    public function setTpl($sTpl);

    /**
     * @return string
     */
    public function getTpl();

    /**
     * @param string $sK
     * @param mixed  $mV
     * @param bool   $bOverwrite
     *
     * @return IAdaptor
     */
    public function assign($sK, $mV, $bOverwrite = true);

    /**
     * @param array $aKVMap
     * @param bool  $bOverwrite
     *
     * @return IAdaptor
     */
    public function assignMulti($aKVMap, $bOverwrite = true);

    /**
     * @return void
     */
    public function render();

    /**
     * @return string
     */
    public function renderAsResult();

    /**
     * @param string $sTpl
     * @param array  $aData
     *
     * @return string
     */
    public function subRender($sTpl, array $aData = array());
}