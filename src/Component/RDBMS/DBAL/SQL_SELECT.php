<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class SQL_SELECT
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class SQL_SELECT extends SQL
{
    /**
     * @var null | array
     */
    protected $naGroupBy = null;

    /**
     * @var null | array
     */
    protected $naField = null;

    /**
     * @var null | Condition
     */
    protected $nHaving = null;

    /**
     * @var null | array
     */
    protected $naDFTField = null;

    /**
     * @var null|int
     */
    protected $niLockType = null;

    /**
     * @var null|string
     */
    protected $nsAlias = null;

    /**
     * @param string $sTable_SQLSEL
     * @param null|array $naDFTField
     */
    public function __construct($sTable_SQLSEL, array $naDFTField = null)
    {
        $this->sTable_SQLSEL = $sTable_SQLSEL;
        if ($naDFTField !== null) {
            $this->naDFTField = $naDFTField;
        }
    }

    /**
     * @param string | V $sField_V
     *
     * multi param as param one
     *
     * @return $this
     */
    public function fields($sField_V)
    {
        $this->naField = func_get_args();
        return $this;
    }

    /**
     * @param string | V $sGroupBy_V
     *
     * multi param as param one
     *
     * @return $this
     */
    public function groupBy($sGroupBy_V)
    {
        $aArr            = func_get_args();
        $this->naGroupBy = $this->naGroupBy === null ? $aArr : array_merge($this->naGroupBy, $aArr);
        return $this;
    }

    /**
     * @param Condition $Having
     *
     * @return $this
     */
    public function having($Having)
    {
        $this->nHaving = $Having;
        return $this;
    }

    /**
     * @param string $sAlias
     *
     * @return $this
     */
    public function alias($sAlias)
    {
        $this->nsAlias = $sAlias;
        return $this;
    }

    /**
     * @param int $iLockType 0:lock in share mode; 1: for update
     * @return $this
     */
    public function lock($iLockType = 0)
    {
        $this->niLockType = $iLockType;
        return $this;
    }

    protected function parseField()
    {
        if (empty($this->naField)) {
            return $this->naDFTField === null ?
                '*' : $this->sQuote . implode("{$this->sQuote},{$this->sQuote}", $this->naDFTField) . $this->sQuote;
        }
        $aField = array();
        foreach ($this->naField as $mItem) {
            if ($mItem instanceof BindItem && $this->m_n_Bind === null) {
                $this->aBindField[$mItem->sK] = $mItem->sK;
                if ($this->m_n_Bind === null) {
                    $this->m_n_Bind = $mItem->Bind;
                }
            }

            $aField[] = is_string($mItem) && strpos($mItem, '.') === false ?
                "{$this->sQuote}$mItem{$this->sQuote}" : (string)$mItem;
        }
        return implode(',', $aField);
    }

    protected function parseGroupBy()
    {
        if ($this->naGroupBy === null) {
            return null;
        }

        $aGroupBy = array();
        foreach ($this->naGroupBy as $mItem) {
            if ($mItem instanceof BindItem) {
                $this->aBindField[$mItem->sK] = $mItem->sK;
                if ($this->m_n_Bind === null) {
                    $this->m_n_Bind = $mItem->Bind;
                }
            }
            $aGroupBy[] = is_string($mItem) && strpos($mItem, '.') === false ?
                "{$this->sQuote}$mItem{$this->sQuote}" : (string)$mItem;
        }
        return implode(',', $aGroupBy);
    }

    protected function parseLockType()
    {
        if ($this->niLockType === null) {
            return null;
        }

        return $this->niLockType === 1 ? 'FOR UPDATE' : 'LOCK IN SHARE MODE';
    }

    /**
     * @param null|string $nsTable
     *
     * @return string
     */
    protected function parseTable($nsTable = null)
    {
        if ($this->sTable_SQLSEL instanceof SQL_SELECT) {
            $sParsedTable = '(' . (string)$this->sTable_SQLSEL . ')';
            if ($this->sTable_SQLSEL->nsAlias !== null) {
                $sParsedTable .= ' AS ' . $this->sQuote . $this->sTable_SQLSEL->nsAlias . $this->sQuote;
            }
            return $sParsedTable;
        } else {
            return parent::parseTable($nsTable);
        }
    }

    public function build()
    {
        $this->m_n_sSQL = sprintf(
            "SELECT %s FROM %s%s%s%s%s%s%s%s%s",
            $this->parseField(),
            $this->parseTable(),
            ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
            $this->nWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->nWhere),
            $this->naGroupBy === null ? '' : ' GROUP BY ' . implode(',', $this->naGroupBy),
            $this->nHaving === null ? '' : ' HAVING ' . $this->parseCondition($this->nHaving),
            ($nsOrder = $this->parseOrder()) === null ? '' : " ORDER BY {$nsOrder}",
            $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
            $this->niOffset === null ? '' : " OFFSET {$this->niOffset}",
            ($nsLock = $this->parseLockType())===null ? '' : " $nsLock"
        );
    }
}
