<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class SQL_DELETE
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class SQL_DELETE extends SQL
{
    public function build()
    {
        $this->m_n_sSQL = sprintf(
            "DELETE FROM %s%s%s%s%s",
            $this->parseTable(),
            ($nsJoin = $this->parseJoin()) === null ? '' : " $nsJoin",
            $this->nWhere === null ? '' : ' WHERE ' . $this->parseCondition($this->nWhere),
            $this->naOrder === null ? '' : ' ORDER BY ' . implode(' ', $this->naOrder),
            $this->niLimit === null ? '' : " LIMIT {$this->niLimit}",
            $this->niOffset === null ? '' : " OFFSET {$this->niOffset}"
        );
    }
}