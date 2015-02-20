<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class SQL_INSERT
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class SQL_INSERT extends SQL
{
    /**
     * @var null | SQL_SELECT
     */
    protected $nSEL = null;

    /**
     * @param SQL_SELECT $SEL
     *
     * @return $this
     */
    public function setSubSEL($SEL)
    {
        $this->nSEL = $SEL;
        return $this;
    }

    const TYPE_IGNORE = 1;
    const TYPE_UPDATE = 2;
    const TYPE_REPLACE = 3;

    protected $niType = null;
    protected $nWhere = null;

    /**
     * @param int          $iType   SQL_INSERT::TYPE_IGNORE / SQL_INSERT::TYPE_UPDATE / SQL_INSERT::TYPE_REPLACE
     * @param null | array $naWhere if iType is TYPE_UPDATE , declare as condition(kv map)
     *
     * @return $this
     */
    public function insertType($iType, $naWhere = null)
    {
        $this->niType = $iType;
        if ($naWhere !== null) {
            $this->nWhere = $naWhere;
        }

        return $this;
    }

    protected $naKey;
    protected $aData = array();

    /**
     * @param array $aKV
     *
     * @return $this
     */
    public function values($aKV)
    {
        $this->m_n_sSQL !== null && $this->m_n_sSQL = null;
        if ($this->naKey === null && is_string(key($aKV))) {
            $this->naKey = array_keys($aKV);
        }
        $this->aData[] = $aKV;

        return $this;
    }

    /**
     * @param array $aKey
     *
     * @return $this
     */
    public function keys($aKey)
    {
        $this->m_n_sSQL !== null && $this->m_n_sSQL = null;
        $this->naKey = $aKey;

        return $this;
    }

    protected function parseData()
    {
        if ($this->nSEL !== null) {
            return (string)$this->nSEL;
        }

        $aTidy = array();
        foreach ($this->aData as $aRow) {
            $aV = array();
            foreach ($aRow as $mV) {
                if ($mV instanceof BindItem) {
                    $this->aBindField[$mV->sK] = $mV->sK;
                    if ($this->m_n_Bind === null) {
                        $this->m_n_Bind = $mV->Bind;
                    }

                }
                $aV[] = is_string($mV) ? "'$mV'" : (string)$mV;
            }
            $aTidy[] = implode(',', $aV);
        }

        switch (count($aTidy)) {
            case 0:
                return null;
            case 1:
                return $aTidy[0];
            default:
                return implode('),(', $aTidy);
        }
    }

    protected function parseKey()
    {
        if ($this->naKey === null) {
            return null;
        }

        $aTidy = array();
        foreach ($this->naKey as $mItem) {
            $aTidy[] = is_string($mItem) && strpos($mItem, '.') === false ? "{$this->sQuote}$mItem{$this->sQuote}" : (string)$mItem;
        }

        return '(' . implode(',', $aTidy) . ')';
    }

    public function build()
    {
        $this->m_n_sSQL = sprintf(
            "%s INTO %s%s VALUES (%s)%s",
            $this->niType === self::TYPE_IGNORE ? 'INSERT IGNORE' : ($this->niType === self::TYPE_REPLACE ? 'REPLACE' : 'INSERT'),
            $this->parseTable(),
            ($nsKey = $this->parseKey()) === null ? '' : " $nsKey",
            $this->parseData(),
            $this->niType === self::TYPE_UPDATE ?
                (' ON DUPLICATE KEY UPDATE ' . $this->parseCondition($this->nWhere)) : ''
        );
    }
}