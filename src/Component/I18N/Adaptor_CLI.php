<?php
namespace Slime\Component\I18N;

/**
 * Class Adaptor_CLI
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
class Adaptor_CLI extends Adaptor_ABS
{
    public static $aCB_GetLang = array('Slime\\Component\\I18N\\Adaptor_CLI', 'getLang');

    public function __construct($sDefaultLang)
    {
        parent::__construct($sDefaultLang);
        $this->_mCB = self::$aCB_GetLang;
    }

    public static function getLangFromCliArgv($sDefaultLang)
    {
        $aArr = getopt('', array('lang::'));
        return empty($aArr['lang']) ? $sDefaultLang : $aArr['lang'];
    }

    /**
     * @return string
     */
    public function getCurrentLang()
    {
        if ($this->nsCurrentLang === null) {
            $this->nsCurrentLang = call_user_func($this->_getCB(), $this->sDefaultLang);
        }

        return $this->nsCurrentLang;
    }


    /** @var mixed */
    private $_mCB = null;

    /**
     * @param mixed $mCB
     */
    public function _setCB($mCB)
    {
        $this->_mCB = $mCB;
    }

    public function _getCB()
    {
        if ($this->_mCB === null) {
            throw new \RuntimeException('[I18N] ; Callback for getCurrentLang is not set before');
        }

        return $this->_mCB;
    }
}