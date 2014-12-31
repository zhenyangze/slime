<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DBAL\Engine;
use Slime\Component\RDBMS\DBAL\BindItem;
use Slime\Component\RDBMS\DBAL\Condition;
use Slime\Component\RDBMS\DBAL\EnginePool;
use Slime\Component\RDBMS\DBAL\SQL;
use Slime\Component\RDBMS\DBAL\SQL_DELETE;
use Slime\Component\RDBMS\DBAL\SQL_INSERT;
use Slime\Component\RDBMS\DBAL\SQL_SELECT;
use Slime\Component\RDBMS\DBAL\V;

/**
 * Class Model
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 *
 * @property-read string     $sMName
 * @property-read string     $sTable
 * @property-read string     $sPKName
 * @property-read string     $sFKName
 * @property-read array      $aRelConf
 * @property-read null|array $naField
 * @property-read bool       $bUseFull
 *
 * @property-read EnginePool $EnginePool
 * @property-read Engine     $Engine
 * @property-read Factory    $Factory
 * @property-read string     $sItemClass
 */
class Model
{
    protected $Factory;
    protected $EnginePool;
    protected $Engine;
    protected $sMName;

    protected $nsDB;
    protected $sItemClass;
    protected $sTable;
    protected $sPKName;
    protected $sFKName;
    protected $aRelConf;
    protected $naField;
    protected $bUseFull;

    protected $nsFKNameTmp = null;

    public function __get($sK)
    {
        return $this->$sK;
    }

    /**
     * @param Factory    $Factory
     * @param string     $sMName
     * @param string     $sItemClass
     * @param EnginePool $EnginePool
     * @param string     $sDB
     * @param array      $naConf
     *
     * @throws \RuntimeException
     */
    public function __construct($Factory, $sMName, $sItemClass, $EnginePool, $sDB, array $naConf = null)
    {
        $this->Factory    = $Factory;
        $this->sMName     = $sMName;
        $this->sItemClass = $sItemClass;
        $this->Engine     = $EnginePool->get($this->nsDB === null ? $sDB : $this->nsDB);
        if ($this->sItemClass === null) {
            $this->sItemClass = $sItemClass;
        }
        if ($this->sTable === null) {
            $this->sTable = isset($naConf['table']) ? $naConf['table'] : strtolower($sMName);
        }
        if ($this->sPKName === null) {
            $this->sPKName = isset($naConf['pk']) ? $naConf['pk'] : 'id';
        }
        if ($this->sFKName === null) {
            $this->sFKName = isset($naConf['fk']) ? $naConf['fk'] : $this->sTable . '_id';
        }
        if ($this->aRelConf === null) {
            $this->aRelConf = isset($naConf['relation']) ? $naConf['relation'] : array();
        }
        if ($this->naField === null) {
            $this->naField = isset($naConf['fields']) ? $naConf['fields'] : null;
        }
        if ($this->bUseFull === null) {
            $this->bUseFull = !empty($naConf['use_full_field_in_select']);
        }
    }

    public function SQL_INS()
    {
        return SQL::INS($this->sTable);
    }

    public function SQL_UPD()
    {
        return SQL::UPD($this->sTable);
    }

    public function SQL_SEL()
    {
        return SQL::SEL($this->sTable, $this->bUseFull ? $this->naField : null);
    }

    public function SQL_DEL()
    {
        return SQL::DEL($this->sTable);
    }

    /**
     * @param string $sFKName
     */
    public function setFKTmp($sFKName)
    {
        $this->nsFKNameTmp = $this->sFKName;
        $this->sFKName     = $sFKName;
    }

    public function resetFK()
    {
        if ($this->nsFKNameTmp !== null) {
            $this->sFKName     = $this->nsFKNameTmp;
            $this->nsFKNameTmp = null;
        }
    }

    /**
     * @param mixed $mSQL
     */
    public function beginTransaction($mSQL = null)
    {
        $this->Engine->inst($mSQL)->beginTransaction();
    }

    /**
     * @param mixed $mSQL
     */
    public function commit($mSQL = null)
    {
        $this->Engine->inst($mSQL)->commit();
    }

    /**
     * @param mixed $mSQL
     */
    public function rollback($mSQL = null)
    {
        $this->Engine->inst($mSQL)->rollBack();
    }

    /**
     * @param array|SQL_INSERT $m_aKVData_SQL
     * @param mixed            $mCBBeforeQ
     *
     * @return bool|int
     */
    public function insert($m_aKVData_SQL, $mCBBeforeQ = null)
    {
        if ($m_aKVData_SQL instanceof SQL_INSERT) {
            $SQL = $m_aKVData_SQL;
        } else {
            $SQL = $this->SQL_INS()->values($m_aKVData_SQL);
            if ($mCBBeforeQ !== null) {
                call_user_func($mCBBeforeQ, $SQL);
            }
        }
        return $this->Engine->E($SQL) ? $this->Engine->inst($SQL)->lastInsertId() : false;
    }

    /**
     * @param null|string|int|Condition|SQL $m_n_siPK_Condition_SQL
     * @param array                         $aKVData
     * @param mixed                         $mCBBeforeQ
     *
     * @return bool|int
     */
    public function update($m_n_siPK_Condition_SQL, array $aKVData, $mCBBeforeQ = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_DELETE) {
            $SQL = $m_n_siPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_UPD();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->add($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
            if ($mCBBeforeQ !== null) {
                call_user_func($mCBBeforeQ, $SQL);
            }
        }
        $SQL->setMulti($aKVData);

        return $this->Engine->E($SQL);
    }

    /**
     * @param null|string|int|Condition|SQL $m_n_siPK_Condition_SQL
     * @param mixed                         $mCBBeforeQ
     *
     * @return bool
     */
    public function delete($m_n_siPK_Condition_SQL, $mCBBeforeQ = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_DELETE) {
            $SQL = $m_n_siPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_DEL();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->add($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
            if ($mCBBeforeQ !== null) {
                call_user_func($mCBBeforeQ, $SQL);
            }
        }

        return $this->Engine->E($SQL);
    }

    /**
     * @param Condition|SQL_SELECT|string|int $m_n_siPK_Condition_SQL
     * @param mixed                           $mCBBeforeQ
     *
     * @return Item|CItem|null
     */
    public function find($m_n_siPK_Condition_SQL, $mCBBeforeQ = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_SELECT) {
            $SQL = $m_n_siPK_Condition_SQL;
            $SQL->limit(1);
        } else {
            $SQL = $this->SQL_SEL();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->add($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
            $SQL->limit(1);
            if ($mCBBeforeQ !== null) {
                call_user_func($mCBBeforeQ, $SQL);
            }
        }
        $mItem = $this->Engine->Q($SQL);

        return empty($mItem) ? Factory::newNull() : new $this->sItemClass($mItem[0], $this);
    }

    /**
     * @param Condition|SQL_SELECT|null|array $m_n_aPK_Condition_SQL
     * @param string|BindItem|array           $mOrderBy
     * @param null|int                        $niLimit
     * @param null|int                        $niOffset
     * @param mixed                           $mCBBeforeQ
     *
     * @return Group|Item[]
     */
    public function findMulti(
        $m_n_aPK_Condition_SQL = null,
        $mOrderBy = null,
        $niLimit = null,
        $niOffset = null,
        $mCBBeforeQ = null
    ) {
        $aaData = $this->findCustom($m_n_aPK_Condition_SQL, $mOrderBy, $niLimit, $niOffset, $mCBBeforeQ);

        $Group = new Group($this);
        if (empty($aaData)) {
            return $Group;
        }
        foreach ($aaData as $aRow) {
            $Group[$aRow[$this->sPKName]] = new $this->sItemClass($aRow, $this, $Group);
        }
        return $Group;
    }

    /**
     * @param Condition|SQL_SELECT|null $m_n_aPK_Condition_SQL
     * @param mixed                     $mCBBeforeQ
     *
     * @return int|bool
     */
    public function findCount($m_n_aPK_Condition_SQL = null, $mCBBeforeQ = null)
    {
        if ($m_n_aPK_Condition_SQL instanceof SQL_SELECT) {
            $SQL = $m_n_aPK_Condition_SQL->fields(V::make('count(1) AS total'))->limit(1);
        } else {
            $SQL = $this->SQL_SEL();
            if ($m_n_aPK_Condition_SQL !== null) {
                if (is_array($m_n_aPK_Condition_SQL)) {
                    $SQL->where(Condition::build()->add($this->sPKName, 'IN', $m_n_aPK_Condition_SQL));
                } else {
                    $SQL->where($m_n_aPK_Condition_SQL);
                }
            }
            $SQL->fields(V::make('count(1) AS total'))->limit(1);
            if ($mCBBeforeQ !== null) {
                call_user_func($mCBBeforeQ, $SQL);
            }
        }
        $aItem = $this->Engine->Q($SQL);

        return $aItem === false ? false : $aItem[0]['total'];
    }

    /**
     * @param Condition|SQL_SELECT|null $m_n_Condition_SQL
     * @param string|BindItem|array     $mOrderBy
     * @param int                       $niLimit
     * @param int                       $niOffset
     * @param mixed                     $mCBBeforeQ
     *
     * @return bool|array
     */
    public function findCustom(
        $m_n_Condition_SQL = null,
        $mOrderBy = null,
        $niLimit = null,
        $niOffset = null,
        $mCBBeforeQ = null
    ) {
        if ($m_n_Condition_SQL instanceof SQL_SELECT) {
            $aaData = $this->Engine->Q($m_n_Condition_SQL);
        } else {
            if (is_array($m_n_Condition_SQL)) {
                $m_n_Condition_SQL = Condition::build()->add($this->sPKName, 'IN', $m_n_Condition_SQL);
            }
            $SQL = $this->SQL_SEL();
            if ($m_n_Condition_SQL !== null) {
                $SQL->where($m_n_Condition_SQL);
            }
            if ($mOrderBy !== null) {
                if (is_array($mOrderBy)) {
                    foreach ($mOrderBy as $mItem) {
                        $SQL->orderBy($mItem);
                    }
                } else {
                    $SQL->orderBy($mOrderBy);
                }
            }
            if ($niLimit !== null) {
                $SQL->limit($niLimit);
            }
            if ($niOffset !== null) {
                $SQL->offset($niOffset);
            }
            if ($mCBBeforeQ !== null) {
                call_user_func($mCBBeforeQ, $SQL);
            }
            $aaData = $this->Engine->Q($SQL);
        }

        return $aaData;
    }
}
