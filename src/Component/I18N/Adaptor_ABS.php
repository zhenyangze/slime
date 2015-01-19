<?php
namespace Slime\Component\I18N;

use Slime\Component\Config\IAdaptor as ConfAdaptor;

/**
 * Class Adaptor_ABS
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
abstract class Adaptor_ABS implements IAdaptor
{
    /** @var null|ConfAdaptor */
    protected $nConfigure;

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
     * @param ConfAdaptor $Configure
     */
    public function setConfigure(ConfAdaptor $Configure)
    {
        $this->nConfigure = $Configure;
    }

    /**
     * @return ConfAdaptor
     */
    public function getConfigure()
    {
        if ($this->nConfigure === null) {
            throw new \RuntimeException('[I18N] ; Configure is not set before');
        }

        return $this->nConfigure;
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
        return $this->getConfigure()->get($sKey, $sKey);
    }
}