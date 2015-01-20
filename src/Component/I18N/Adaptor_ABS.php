<?php
namespace Slime\Component\I18N;

use Slime\Component\Config\Adaptor_PHP;
use Slime\Component\Config\IAdaptor as ConfAdaptor;

/**
 * Class Adaptor_ABS
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
abstract class Adaptor_ABS implements IAdaptor
{
    protected $sDefaultLang;
    protected $nsCurrentLang;

    /**
     * @param string $sDefaultLang
     */
    public function __construct($sDefaultLang)
    {
        $this->sDefaultLang = $sDefaultLang;
    }

    /**
     * @return string
     */
    abstract public function getCurrentLang();

    /**
     * @param string $sKey
     *
     * @return string
     */
    public function get($sKey)
    {
        return $this->_getConfigure()->get($sKey, $sKey);
    }


    /** @var null|ConfAdaptor */
    private $_nConfigure = null;

    public function _setConfigure_PHP($sBaseDir)
    {
        $this->_nConfigure = new Adaptor_PHP($sBaseDir, $this->getCurrentLang(), $this->sDefaultLang);
    }

    /**
     * @param ConfAdaptor $Configure
     */
    public function _setConfigure(ConfAdaptor $Configure)
    {
        $this->_nConfigure = $Configure;
    }

    /**
     * @return ConfAdaptor
     */
    public function _getConfigure()
    {
        if ($this->_nConfigure === null) {
            throw new \RuntimeException('[I18N] ; Configure is not set before');
        }

        return $this->_nConfigure;
    }
}