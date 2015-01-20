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
    protected $aCachedData = null;

    /**
     * @param string $sTable
     * @param string $sFieldKey
     * @param string $sFieldValue
     */
    public function __construct($sTable, $sFieldKey = 'key', $sFieldValue = 'value')
    {
        $this->sTable  = $sTable;
        $this->sFieldK = $sFieldKey;
        $this->sFieldV = $sFieldValue;
    }

    /**
     * @param string $sKey
     * @param mixed  $mDefault
     * @param bool   $bForce
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function get($sKey, $mDefault = null, $bForce = false)
    {
        if ($this->aCachedData === null) {
            $aArr              = $this->_getEngine()->Q(SQL_SELECT::SEL($this->sTable));
            $this->aCachedData = empty($aArr) ?
                array() : Arr::changeIndexToKVMap($aArr, $this->sFieldK, $this->sFieldV);
        }
        if (!isset($this->aCachedData[$sKey])) {
            if ($bForce) {
                throw new \OutOfBoundsException("[CONFIG] ; Can not find key[$sKey] in config");
            } else {
                return $mDefault;
            }
        }

        return $this->parse($this->aCachedData[$sKey], $mDefault, $bForce);
    }


    /** @var null|Engine */
    private $_nEngine = null;

    public function _setEngine(Engine $Engine)
    {
        $this->_nEngine = $Engine;
    }

    public function _getEngine()
    {
        if ($this->_nEngine === null) {
            throw new \RuntimeException('[Config] ; Engine is not set before');
        }

        return $this->_nEngine;
    }
}