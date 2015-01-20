<?php
namespace Slime\Component\I18N;

use Slime\Component\Http\REQ;

/**
 * Class Adaptor_Http
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
class Adaptor_Http extends Adaptor_ABS
{
    public static $aLangMapDir = array(
        '#en-.*#' => 'english',
        '#zh-.*#' => 'zh-cn'
    );

    /**
     * @param string      $sDefaultLang
     * @param null|string $nsCookieKey
     */
    public function __construct($sDefaultLang, $nsCookieKey = null)
    {
        parent::__construct($sDefaultLang);
        $this->nsCookieKey = $nsCookieKey;
    }

    /**
     * @return string
     */
    public function getCurrentLang()
    {
        if ($this->nsCurrentLang !== null) {
            goto END;
        }

        $REQ = $this->_getREQ();

        # from cookie
        if ($this->nsCookieKey !== null) {
            $nsLangFromC = $REQ->getC($this->nsCookieKey);
            if ($nsLangFromC !== null) {
                $this->nsCurrentLang = $nsLangFromC;
                goto END;
            }
        }

        # from header
        $nsLangFromH = $REQ->getHeader('Accept_Language');
        if ($nsLangFromH === null) {
            $this->nsCurrentLang = $this->sDefaultLang;
            goto END;
        }
        $nsLangDir = null;
        foreach (self::$aLangMapDir as $sK => $sV) {
            if (preg_match($sK, $nsLangFromH)) {
                $this->nsCurrentLang = $sV;
                goto END;
            }
        }

        $this->nsCurrentLang = $this->sDefaultLang;

        END:
        return $this->nsCurrentLang;
    }


    /** @var null|REQ */
    private $_nREQ = null;

    /**
     * @param REQ $REQ
     */
    public function _setREQ(REQ $REQ)
    {
        $this->_nREQ = $REQ;
    }

    /**
     * @return REQ
     *
     * @throws \RuntimeException
     */
    public function _getREQ()
    {
        if ($this->_nREQ === null) {
            throw new \RuntimeException('[I18N] ; REQ is not set before');
        }

        return $this->_nREQ;
    }
}
