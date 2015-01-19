<?php
namespace Slime\Component\I18N;

use Slime\Component\Http\REQ;

class Adaptor_Http extends Adaptor_ABS
{
    public static $aLangMapDir = array(
        '#en-.*#' => 'english',
        '#zh-.*#' => 'zh-cn'
    );

    /** @var null|REQ */
    protected $nREQ;

    /**
     * @param string $sDefaultLang
     * @param null|string $nsCookieKey
     */
    public function __construct($sDefaultLang, $nsCookieKey = null)
    {
        parent::__construct($sDefaultLang);
        $this->nsCookieKey = $nsCookieKey;
    }

    /**
     * @param REQ $REQ
     */
    public function setREQ(REQ $REQ)
    {
        $this->nREQ = $REQ;
    }

    /**
     * @return REQ
     *
     * @throws \RuntimeException
     */
    public function getREQ()
    {
        if ($this->nREQ === null) {
            throw new \RuntimeException('[I18N] ; REQ is not set before');
        }

        return $this->nREQ;
    }

    /**
     * @return string
     */
    public function getCurrentLang()
    {
        if ($this->nsCurrentLang !== null) {
            goto END;
        }

        $REQ = $this->getREQ();

        # from cookie
        if ($this->nsCookieKey !== null) {
            $nsLangFromC = $REQ->getC($this->nsCookieKey);
            if ($nsLangFromC!==null) {
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
}
