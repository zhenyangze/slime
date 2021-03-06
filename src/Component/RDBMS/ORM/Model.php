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
use Slime\Component\RDBMS\DBAL\SQL_UPDATE;
use Slime\Component\RDBMS\DBAL\V;
use Slime\Component\Support\CompatibleEmpty;

/**
 * Class Model
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 *
 * @property-read EnginePool        $EnginePool
 * @property-read Engine            $Engine
 * @property-read Factory           $Factory
 *
 * @property-read string            $sMName
 * @property-read string            $sItemClass
 *
 * @property-read string            $nsDB
 * @property-read string            $sTable
 * @property-read string            $sPKName
 * @property-read null|string|array $aFKName
 * @property-read array             $aRelConf
 * @property-read null|array        $naField
 * @property-read bool              $bUseFullField
 * @property-read null|array        $naThroughTable
 */
class Model
{
    protected $Factory;
    protected $EnginePool;
    protected $Engine;

    protected $sMName;
    protected $sItemClass;

    protected $nsDB;
    protected $sTable;
    protected $sPKName;
    protected $aFKName;
    protected $aRelConf;
    protected $naField;
    protected $bUseFullField;
    protected $naThroughTable;

    public function __get($sK)
    {
        return $this->$sK;
    }

    /**
     * @param Factory    $Factory
     * @param string     $sMName
     * @param string     $sItemClass
     * @param EnginePool $EnginePool
     * @param array      $aConf
     * @param array      $aDFT
     *
     * @throws \OutOfBoundsException
     */
    public function __construct($Factory, $sMName, $sItemClass, $EnginePool, $aConf, $aDFT)
    {
        $this->Factory    = $Factory;
        $this->sMName     = $sMName;
        $this->sItemClass = $sItemClass;
        $this->Engine     = $EnginePool->get(
            $this->nsDB === null ?
                (isset($aConf['db']) ? $aConf['db'] : $aDFT['db'])
                : $this->nsDB
        );
        if ($this->sItemClass === null) {
            $this->sItemClass = $sItemClass;
        }
        if ($this->sTable === null) {
            if (isset($aConf['table'])) {
                $this->sTable = $aConf['table'];
            } else {
                if (isset($aDFT['cb_table'])) {
                    $this->sTable = call_user_func($aDFT['cb_table'], $sMName);
                } else {
                    $this->sTable = strtolower($sMName);
                }
            }
        }
        if ($this->sPKName === null) {
            $this->sPKName = isset($aConf['pk']) ? $aConf['pk'] : 'id';
        }
        if ($this->aFKName === null) {
            $this->aFKName    = isset($aConf['fk']) ? $aConf['fk'] : array();
            if (!isset($this->aFKName[0])) {
                $this->aFKName[0] = $this->sTable . '_id';
            }
        }
        if ($this->aRelConf === null) {
            $this->aRelConf = isset($aConf['relation']) ? $aConf['relation'] : array();
        }
        if ($this->naField === null) {
            $this->naField = isset($aConf['fields']) ? $aConf['fields'] : null;
        }
        if ($this->bUseFullField === null) {
            $this->bUseFullField = !empty($aConf['use_full_field_in_select']);
        }
        if ($this->naThroughTable === null) {
            $this->naThroughTable = isset($aConf['through_table']) ? $aConf['through_table'] : null;
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
        return SQL::SEL($this->sTable, $this->bUseFullField ? $this->naField : null);
    }

    public function SQL_DEL()
    {
        return SQL::DEL($this->sTable);
    }

    /**
     * @param null|Group $Group
     *
     * @return Item
     */
    public function createItem($Group = null)
    {
        return new $this->sItemClass([], $this, $Group);
    }

    /**
     * @param Model $M
     *
     * @return string
     */
    public function getFKName($M)
    {
        return isset($this->aFKName[$M->sMName]) ? $this->aFKName[$M->sMName] : $this->aFKName[0];
    }

    /**
     * @param Model $M
     *
     * @return string
     */
    public function getThroughTable($M)
    {
        if (isset($this->naThroughTable[$M->sMName])) {
            return $this->naThroughTable[$M->sMName];
        } else {
            return 'rel__' . (
            strcmp($this->sTable, $M->sTable) > 0 ?
                $M->sTable . '__' . $this->sTable :
                $this->sTable . '__' . $M->sTable
            );
        }
    }

    protected $__aCachedTransAuto__ = array();

    public function beginTransaction()
    {
        $PDO                          = $this->Engine->inst();
        $this->__aCachedTransAuto__[] = $PDO->getAttribute(\PDO::ATTR_AUTOCOMMIT);
        $PDO->setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
        $PDO->beginTransaction();
    }

    public function commit()
    {
        $PDO = $this->Engine->inst();
        $PDO->commit();
        if (!empty($this->__aCachedTransAuto__)) {
            $PDO->setAttribute(\PDO::ATTR_AUTOCOMMIT, array_pop($this->__aCachedTransAuto__));
        }
    }

    public function rollback()
    {
        $PDO = $this->Engine->inst();
        $PDO->rollBack();
        if (!empty($this->__aCachedTransAuto__)) {
            $PDO->setAttribute(\PDO::ATTR_AUTOCOMMIT, array_pop($this->__aCachedTransAuto__));
        }
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
        }
        if ($mCBBeforeQ !== null) {
            call_user_func($mCBBeforeQ, $SQL);
        }
        return $this->Engine->E($SQL) ? $this->Engine->inst()->lastInsertId() : false;
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
        if ($m_n_siPK_Condition_SQL instanceof SQL_UPDATE) {
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
        }
        if ($mCBBeforeQ !== null) {
            call_user_func($mCBBeforeQ, $SQL);
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
        }
        if ($mCBBeforeQ !== null) {
            call_user_func($mCBBeforeQ, $SQL);
        }

        return $this->Engine->E($SQL);
    }

    /**
     * @param Condition|SQL_SELECT|string|int $m_n_siPK_Condition_SQL
     * @param mixed                           $mCBBeforeQ
     *
     * @return Item|CompatibleEmpty|null
     */
    public function find($m_n_siPK_Condition_SQL, $mCBBeforeQ = null)
    {
        if ($m_n_siPK_Condition_SQL instanceof SQL_SELECT) {
            $SQL = $m_n_siPK_Condition_SQL;
        } else {
            $SQL = $this->SQL_SEL();
            if ($m_n_siPK_Condition_SQL !== null) {
                $SQL->where(
                    $m_n_siPK_Condition_SQL instanceof Condition ?
                        $m_n_siPK_Condition_SQL :
                        Condition::build()->add($this->sPKName, '=', $m_n_siPK_Condition_SQL)
                );
            }
        }
        $SQL->limit(1);
        if ($mCBBeforeQ !== null) {
            call_user_func($mCBBeforeQ, $SQL);
        }
        $mItem = $this->Engine->Q($SQL);

        return empty($mItem) ? new CompatibleEmpty() : new $this->sItemClass($mItem[0], $this);
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
        }
        if ($mCBBeforeQ !== null) {
            call_user_func($mCBBeforeQ, $SQL);
        }
        $aItem = $this->Engine->Q($SQL);

        return $aItem === false ? false : $aItem[0]['total'];
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
        $aaData = $this->findMultiArray($m_n_aPK_Condition_SQL, $mOrderBy, $niLimit, $niOffset, $mCBBeforeQ);

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
     * @param Condition|SQL_SELECT|null $m_n_Condition_SQL
     * @param string|BindItem|array     $mOrderBy
     * @param int                       $niLimit
     * @param int                       $niOffset
     * @param mixed                     $mCBBeforeQ
     *
     * @return bool|array
     */
    public function findMultiArray(
        $m_n_Condition_SQL = null,
        $mOrderBy = null,
        $niLimit = null,
        $niOffset = null,
        $mCBBeforeQ = null
    ) {
        if ($m_n_Condition_SQL instanceof SQL_SELECT) {
            $SQL = $m_n_Condition_SQL;
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
        }
        if ($mCBBeforeQ !== null) {
            call_user_func($mCBBeforeQ, $SQL);
        }
        $aaData = $this->Engine->Q($SQL);

        return $aaData;
    }
}
