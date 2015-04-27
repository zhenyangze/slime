<?php
namespace Slime\Component\Support;

/**
 * Class Url
 *
 * @package Slime\Component\Support
 * @author  smallslime@gmaile.com
 */
class Url
{
    protected $aBlock;
    protected $iEncType;

    public function __construct($sUrl, $iEncType = PHP_QUERY_RFC1738)
    {
        $this->aBlock   = self::parse($sUrl, true, true);
        $this->iEncType = $iEncType;
    }

    /**
     * @param null|int $niIndex
     *
     * @return string|null
     */
    public function getPathBlock($niIndex = null)
    {
        return $niIndex === null ?
            $this->aBlock['path'] :
            (
                isset($this->aBlock['path'][$niIndex]) ? $this->aBlock['path'][$niIndex] : null
            );
    }

    /**
     * @return int
     */
    public function getPathBlockCount()
    {
        return count($this->aBlock['path']);
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return $this
     */
    public function update($sK, $mV)
    {
        if ($mV === null) {
            unset($this->aBlock[$sK]);
        } else {
            $this->aBlock[$sK] = $mV;
        }

        return $this;
    }

    /**
     * @param array $aArr
     *
     * @return $this
     */
    public function updateQuery(array $aArr)
    {
        $this->aBlock['query'] = empty($this->aBlock['query']) ? $aArr : array_merge($this->aBlock['query'], $aArr);

        return $this;
    }

    /**
     * @param array $aQueryBlock
     * @param array $aOtherBlockKV
     * @param int   $iEncType
     *
     * @return string
     */
    public function getNewUrl(array $aQueryBlock, array $aOtherBlockKV = array(), $iEncType = PHP_QUERY_RFC1738)
    {
        $O                  = clone $this;
        $O->aBlock['query'] = empty($O->aBlock['query']) ? $aQueryBlock : array_merge(
            $O->aBlock['query'],
            $aQueryBlock
        );
        if (!empty($aOtherBlockKV)) {
            $O->aBlock = array_merge($O->aBlock, $aOtherBlockKV);
        }
        if ($O->iEncType !== $iEncType) {
            $O->iEncType = $iEncType;
        }

        return (string)$O;
    }

    public function toString()
    {
        return (string)$this;
    }

    public function __toString()
    {
        return self::build($this->aBlock, $this->iEncType);
    }

    public static function parse($sUrl, $bParsePath = true, $bParseQuery = true)
    {
        $aBlock = parse_url($sUrl);
        if ($bParsePath) {
            $aBlock['path'] = isset($aBlock['path']) ? explode('/', ltrim($aBlock['path'], '/')) : array();
        }
        if ($bParseQuery) {
            if (isset($aBlock['query'])) {
                parse_str($aBlock['query'], $aBlock['query']);
            } else {
                $aBlock['query'] = array();
            }
        }

        return $aBlock;
    }

    /**
     * @param array $aBlock
     * @param int   $iBuildQueryEncTypeIfQueryIsArr
     *
     * @return string
     */
    public static function build($aBlock, $iBuildQueryEncTypeIfQueryIsArr = PHP_QUERY_RFC1738)
    {
        $sScheme   = isset($aBlock['scheme']) ? $aBlock['scheme'] . '://' : '';
        $sHost     = isset($aBlock['host']) ? $aBlock['host'] : '';
        $sPort     = isset($aBlock['port']) ? ':' . $aBlock['port'] : '';
        $sUser     = isset($aBlock['user']) ? $aBlock['user'] : '';
        $sPass     = isset($aBlock['pass']) ? ":{$aBlock['pass']}" : '';
        $sPass     = ($sUser === '' && $sPass === '') ? '' : "$sPass@";
        $sPath     = isset($aBlock['path']) ?
            (
            is_array($aBlock['path']) ?
                '/' . implode('/', $aBlock['path']) :
                $aBlock['path']
            )
            : '';
        $sQuery    = isset($aBlock['query']) ?
            '?' . (
            is_array($aBlock['query']) ?
                http_build_query($aBlock['query'], null, '&', $iBuildQueryEncTypeIfQueryIsArr) :
                $aBlock['query']
            )
            : '';
        $sFragment = isset($aBlock['fragment']) ? '#' . $aBlock['fragment'] : '';

        return "{$sScheme}{$sUser}{$sPass}{$sHost}{$sPort}{$sPath}{$sQuery}{$sFragment}";
    }

}
