<?php
namespace Slime\Component\I18N;

/**
 * Class I18N
 *
 * @package Slime\Component\I18N
 * @author  smallslime@gmail.com
 */
class I18N
{
    public static $aLangMapDir = array(
        '#en-.*#' => 'english',
        '#zh-.*#' => 'zh-cn'
    );

    /** @var \Slime\Component\Config\IAdaptor */
    protected $Obj;

    /**
     * @param \Slime\Component\Http\REQ $REQ
     * @param string                    $sBaseDir
     * @param string                    $sDefaultLangDir
     * @param string                    $sCookieKey
     *
     * @return I18N
     */
    public static function createFromHttp(
        $REQ,
        $sBaseDir,
        $sDefaultLangDir = 'english',
        $sCookieKey = null
    ) {
        $nsLangFromC = null;
        if ($sCookieKey !== null) {
            $nsLangFromC = $REQ->getC($sCookieKey);
        }
        $nsLangFromH = $REQ->getHeader('Accept_Language');
        if (empty($nsLangFromC)) {
            if ($nsLangFromH === null) {
                $sLang = 'en-us';
            } else {
                $sLang = strtolower(strtok($nsLangFromH, ','));
            }
        } else {
            $sLang = $nsLangFromC;
        }
        $nsLangDir = self::_getCurLang($sLang);
        $sDefaultPath = $sBaseDir . '/' . $sDefaultLangDir;

        return new self('@PHP', $nsLangDir === null ? $sDefaultPath : $sBaseDir . '/' . $nsLangDir, $sDefaultPath);
    }

    public static function createFromCli(array $aArg, $sBaseDir, $sDefaultLangDir = 'english')
    {
        $sLang = $aArg[count($aArg) - 1];
        if (array_search($sLang, self::$aLangMapDir) === false) {
            $sLang = $sDefaultLangDir;
        }
        $nsLangDir = self::_getCurLang($sLang);
        $sDefaultPath = $sBaseDir . '/' . $sDefaultLangDir;

        return new self('@PHP', $nsLangDir === null ? $sDefaultPath : $sBaseDir . '/' . $nsLangDir, $sDefaultPath);
    }

    protected static function _getCurLang($sLang)
    {
        $nsLangDir = null;
        foreach (self::$aLangMapDir as $sK => $sV) {
            if (preg_match($sK, $sLang)) {
                $nsLangDir = $sV;
                break;
            }
        }

        return $nsLangDir;
    }

    public function __construct($sConfigAdaptor)
    {
        $this->Obj = call_user_func_array(
            array('\\Slime\\Component\\Config\\Configure', 'factory'),
            func_get_args()
        );
    }

    public function get($sString)
    {
        return $this->Obj->get($sString, $sString, false);
    }
}