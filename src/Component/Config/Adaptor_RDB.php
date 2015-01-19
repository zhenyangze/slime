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

    /** @var null|Engine */
    protected $nEngine;

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

    public function setEngine(Engine $Engine)
    {
        $this->nEngine = $Engine;
    }

    public function getEngine()
    {
        if ($this->nEngine === null) {
            throw new \RuntimeException('[Config] ; Engine is not set before');
        }

        return $this->nEngine;
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
            $aArr              = $this->getEngine()->Q(SQL_SELECT::SEL($this->sTable));
            $this->aCachedData = empty($aArr) ?
                array() : Arr::changeIndexToKVMap($aArr, $this->sFieldK, $this->sFieldV);
        }
        if (!isset($this->aCachedData[$sKey])) {
            if ($bForce) {
                throw new \OutOfBoundsException("[CONFIG] ; can not find key[$sKey] in config");
            } else {
                return $mDefault;
            }
        }

        return $this->parse($this->aCachedData[$sKey], $mDefault, $bForce);
    }
}