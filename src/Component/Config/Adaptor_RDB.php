<?php
namespace Slime\Component\Config;

use Slime\Component\RDBMS\DBAL\SQL_SELECT;
use Slime\Component\RDBMS\DBAL\Engine;
use Slime\Component\Support\Arr;

/**
 * Class Configure
 *
 * @package Slime\Component\Config
 * @author  smallslime@gmail.com
 */
class Adaptor_RDB extends Adaptor_ABS
{
    protected $sTable;
    protected $sFieldK;
    protected $sFieldV;

    /** @var array */
    protected $aData = null;

    /**
     * @param Engine $PDO
     * @param string $sTable
     * @param string $sFieldKey
     * @param string $sFieldValue
     */
    public function __construct($PDO, $sTable, $sFieldKey = 'key', $sFieldValue = 'value')
    {
        $this->PDO     = $PDO;
        $this->sTable  = $sTable;
        $this->sFieldK = $sFieldKey;
        $this->sFieldV = $sFieldValue;
    }

    /**
     * @param string $sKey
     * @param mixed  $mDefault
     * @param bool   $bWithParse
     *
     * @return mixed
     */
    public function get($sKey, $mDefault = null, $bWithParse = false)
    {
        if ($this->aData === null) {
            $aArr        = $this->PDO->Q(SQL_SELECT::SEL($this->sTable));
            $this->aData = empty($aArr) ? array() : Arr::changeIndexToKVMap($aArr, $this->sFieldK, $this->sFieldV);
        }
        if (!isset($this->aData[$sKey])) {
            return null;
        }

        $mResult = $this->aData[$sKey];
        if ($bWithParse) {
            $mResult = $this->parse($mResult, false);
        }

        return $mResult===null ? $mDefault : $mResult;
    }

}