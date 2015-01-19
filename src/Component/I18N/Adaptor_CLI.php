<?php
namespace Slime\Component\I18N;

class Adaptor_CLI extends Adaptor_ABS
{
    public static $aCB_GetLang = array('Slime\\Component\\I18N\\Adaptor_CLI', 'getLang');

    public static function getLangFromCliArgv($sDefaultLang)
    {
        $aArr = getopt('', array('lang::'));
        return empty($aArr['lang']) ? $sDefaultLang : $aArr['lang'];
    }

    protected $mCB;

    /**
     * @return string
     */
    public function getCurrentLang()
    {
        if ($this->nsCurrentLang === null) {
            $this->nsCurrentLang = call_user_func($this->mCB, $this->sDefaultLang);
        }

        return $this->nsCurrentLang;
    }

    /**
     * @param mixed $mCB null means default [Slime\Component\I18N\Adaptor_CLI::$aCB_GetLang]
     */
    public function setCB($mCB = null)
    {
        $this->mCB = $mCB === null ? self::$aCB_GetLang : $mCB;
    }

    public function getCB()
    {
        if ($this->mCB === null) {
            throw new \RuntimeException('[I18N] ; callback for getCurrentLang is not set before');
        }

        return $this->mCB;
    }
}