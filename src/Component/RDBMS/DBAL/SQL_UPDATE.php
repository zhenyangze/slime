<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class SQL_UPDATE
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class SQL_UPDATE extends SQL
{
    protected $aMap = array();

    /**
     * @param string $sK
     * @param mixed  $mV string | int | float | Val
     *
     * @return $this
     */
    public function set($sK, $mV)
    {
        $this->m_n_sSQL !== null && $this->m_n_sSQL = null;
        $this->aMap[$sK] = $mV;
        return $this;
    }

    /**
     * @param array $aKV
     *
     * @return $this
     */
    public function setMulti($aKV)
    {
        $this->m_n_sSQL !== null && $this->m_n_sSQL = null;
        $this->aMap = array_merge($this->aMap, $aKV);

        return $this;
    }

    public function build()
    {
        $this->m_n_sSQL = sprintf(
            'UPDATE %s%s SET %s%s%s%s%s',
            $this->parseTable(),
            ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
            $this->parseUpdateMap($this->aMap),
            $this->nWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->nWhere),
            ($nsOrder = $this->parseOrder()) === null ? '' : " ORDER BY {$nsOrder}",
            $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
            $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
        );
    }
}