<?php
namespace Slime\Component\Http;

use Slime\Component\Support\CB;

/**
 * Class RESP
 *
 * @package Slime\Component\Http
 * @author  smallslime@gmail.com
 */
class RESP
{
    protected $niStatus = null;
    protected $sProtocol = null;
    protected $aHeader = array();
    protected $nsBody;
    protected $aCookie = array();

    /**
     * @param string      $sProtocol
     * @param int         $niStatus
     * @param null|array  $naHeader
     * @param null|string $nsBody
     */
    public function __construct($sProtocol, $niStatus = null, array $naHeader = null, $nsBody = null)
    {
        $this->sProtocol = $sProtocol;

        if ($niStatus !== null) {
            $this->setStatus($niStatus);
        }

        if ($naHeader !== null) {
            $this->addHeader($naHeader);
        }

        if ($nsBody !== null) {
            $this->setBody($nsBody);
        }
    }

    /**
     * @param null|string|array $nasK
     * @param bool              $bNotNullFill
     *
     * @return null|string|array
     */
    public function getHeader($nasK = null, $bNotNullFill = false)
    {
        return $this->_getData($nasK, $this->aHeader, $bNotNullFill);
    }

    /**
     * @param array|string $m_aKV_sK
     * @param null|string  $m_n_sV
     *
     * @return $this
     */
    public function addHeader($m_aKV_sK, $m_n_sV = null)
    {
        if (is_array($m_aKV_sK)) {
            foreach ($m_aKV_sK as $sK => $mV) {
                if (is_array($mV)) {
                    $this->aHeader[$sK] = array_merge($this->aHeader[$sK], $mV);
                } else {
                    $this->aHeader[$sK][] = $mV;
                }
            }
        } else {
            $this->aHeader[$m_aKV_sK][] = $m_n_sV;
        }

        return $this;
    }

    /**
     * @param array|string $m_aKV_sK
     * @param null|string  $m_n_sV
     *
     * @return $this
     */
    public function setHeader($m_aKV_sK, $m_n_sV = null)
    {
        if (is_array($m_aKV_sK)) {
            $this->aHeader = array_merge($this->aHeader, $m_aKV_sK);
        } else {
            $this->aHeader[$m_aKV_sK] = $m_n_sV;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->nsBody;
    }

    /**
     * @param string $sBody You can set a object witch can be stringed or iterator
     *
     * @return $this
     */
    public function setBody($sBody)
    {
        $this->nsBody = $sBody;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getStatus()
    {
        return $this->niStatus;
    }

    /**
     * @param int $iStatus
     *
     * @return $this
     */
    public function setStatus($iStatus)
    {
        $this->niStatus = $iStatus;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getStatusMessage()
    {
        static $aStatusTexts = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // RFC2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', // RFC4918
            208 => 'Already Reported', // RFC5842
            226 => 'IM Used', // RFC3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect', // RFC-reschke-http-status-308-07
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC2324
            422 => 'Unprocessable Entity', // RFC4918
            423 => 'Locked', // RFC4918
            424 => 'Failed Dependency', // RFC4918
            425 => 'Reserved for WebDAV advanced collections expired proposal', // RFC2817
            426 => 'Upgrade Required', // RFC2817
            428 => 'Precondition Required', // RFC6585
            429 => 'Too Many Requests', // RFC6585
            431 => 'Request Header Fields Too Large', // RFC6585
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates (Experimental)', // RFC2295
            507 => 'Insufficient Storage', // RFC4918
            508 => 'Loop Detected', // RFC5842
            510 => 'Not Extended', // RFC2774
            511 => 'Network Authentication Required', // RFC6585
        );

        return isset($aStatusTexts[$this->niStatus]) ? $aStatusTexts[$this->niStatus] : null;
    }

    /**
     * @return null|string
     */
    public function getProtocol()
    {
        return $this->sProtocol;
    }

    /**
     * @param string
     *
     * @return $this
     */
    public function setProtocol($sProtocol)
    {
        $this->sProtocol = $sProtocol;

        return $this;
    }

    /**
     * @param string      $sName
     * @param string      $sValue
     * @param null|int    $niExpires
     * @param null|string $nsPath
     * @param null|string $nsDomain
     * @param null|bool   $nbSecure
     * @param null|bool   $nbHttpOnly
     * @param null|array  $naOther
     *
     * @return $this
     */
    public function setCookie(
        $sName,
        $sValue,
        $niExpires = null,
        $nsPath = null,
        $nsDomain = null,
        $nbSecure = null,
        $nbHttpOnly = null,
        $naOther = null
    ) {
        $this->aCookie[] = array(
            'name'      => $sName,
            'value'     => $sValue,
            'Expires'   => $niExpires,
            'Path'      => $nsPath,
            'Domain'    => $nsDomain,
            'Secure'    => $nbSecure,
            'HttpOnly'  => $nbHttpOnly,
            '__Other__' => $naOther
        );

        return $this;
    }

    public function send()
    {
        $this->sendHeader();
        $this->sendBody();
    }

    public function sendHeader()
    {
        if (headers_sent()) {
            trigger_error('[HTTP]; Header has sent before', E_USER_NOTICE);
        } else {
            // first line
            if ($this->niStatus !== null && $this->niStatus !== 200) {
                header(sprintf('%s %d %s', $this->sProtocol, $this->niStatus, $this->getStatusMessage()));
            }

            if (!empty($this->aHeader)) {
                foreach ($this->aHeader as $sK => $mRow) {
                    foreach ((array)$mRow as $sItem) {
                        if ($sItem) {
                            header("$sK: $sItem");
                        }
                    }
                }
            }

            if (!empty($this->aCookie)) {
                foreach ($this->aCookie as $aArr) {
                    if (isset($aArr['__Other__'])) {
                        $sK     = array_shift($aArr);
                        $sN     = array_shift($aArr);
                        $aOther = array_pop($aArr);
                        $aTidy  = array("$sK=$sN");
                        foreach (array_merge($aArr, $aOther) as $sK => $mV) {
                            if ($mV === null) {
                                continue;
                            }
                            $aTidy[] = is_bool($mV) ? $sK : "$sK=$mV";
                        }
                        header(sprintf('Set-Cookie: %s', implode('; ', $aTidy)));
                    } else {
                        unset($aArr['__Other__']);
                        call_user_func_array('setcookie', $aArr);
                    }
                }
            }
        }
    }

    public function sendBody()
    {
        if (is_object($this->nsBody) || $this->nsBody instanceof \Traversable) {
            foreach ($this->nsBody as $sPart) {
                echo $sPart;
            }
        } else {
            if ($this->nsBody !== null) {
                echo $this->nsBody;
            }
        }
    }

    /**
     * @param null|string|array $nasK
     * @param array             $aData
     * @param bool              $bNotNullFill
     *
     * @return null|string|array
     */
    protected function _getData($nasK, $aData, $bNotNullFill)
    {
        if ($nasK === null) {
            return $aData;
        } elseif (is_array($nasK)) {
            if ($bNotNullFill) {
                return array_intersect_key(array_flip($nasK), $aData);
            } else {
                $aRS = array();
                foreach ($nasK as $sK) {
                    $aRS[$sK] = isset($aData[$sK]) ? $aData[$sK] : null;
                }
                return $aRS;
            }
        } else {
            return isset($aData[$sK = (string)$nasK]) ? $aData[$sK] : null;
        }
    }

    public function setNoCache()
    {
        $this->addHeader(
            array(
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 26 Jul 1997 05:00:00 GMT',
                'Cache-Control' => 'no-cache, no-store, must-revalidate'
            )
        );
    }

    /**
     * @param string   $sUrl
     * @param null|int $niCode
     *
     * @return $this
     */
    public function setRedirect($sUrl, $niCode = null)
    {
        $this->addHeader('Location', $sUrl);
        if ($niCode !== null) {
            $this->setStatus($niCode);
        }

        return $this;
    }

    /**
     * @param int         $iStatus
     * @param null|string $nsMSG
     */
    public function throwError($iStatus, $nsMSG = null)
    {
        $nErrorHandle = $this->_getErrorHandle();
        if (empty($nErrorHandle)) {
            $this->setStatus($iStatus)->setBody($nsMSG === null ? $this->getStatusMessage() : (string)$nsMSG);
            return;
        }

        $nErrorHandle->call($iStatus, $nsMSG);
    }

    /** @var null|CB */
    private $nCBErrorHandle;

    /**
     * @param CB $CB
     */
    public function _setErrorHandle(CB $CB)
    {
        $this->nCBErrorHandle = $CB;
    }

    /**
     * @return null|CB
     */
    public function _getErrorHandle()
    {
        return $this->nCBErrorHandle;
    }
}