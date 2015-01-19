<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DBAL\Condition;
use Slime\Component\RDBMS\DBAL\SQL_SELECT;
use Slime\Component\RDBMS\DBAL\V;

/**
 * Class Item
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 *
 * @property-read Model      $__M__
 * @property-read Group|null $__Group__
 */
class Item implements \ArrayAccess
{
    /** @var Model */
    public $__M__;

    /** @var Group|null */
    public $__Group__;

    /** @var array */
    protected $aData;

    /** @var array */
    protected $aOldData = array();

    /**
     * @param array        $aData
     * @param Model        $Model
     * @param Group | null $Group
     */
    public function __construct(array $aData, $Model, $Group = null)
    {
        $this->aData     = $aData;
        $this->__M__     = $Model;
        $this->__Group__ = $Group;
    }

    public function __get($sKey)
    {
        return isset($this->aData[$sKey]) ? $this->aData[$sKey] : null;
    }

    public function __set($sKey, $mValue)
    {
        $this->_set($sKey, $mValue);
    }

    /**
     * @param array $aKVMap
     */
    public function set(array $aKVMap)
    {
        $this->_set($aKVMap);
    }

    protected function _set($mK, $mV = null)
    {
        if (is_array($mK)) {
            foreach ($mK as $sKey => $sValue) {
                if (array_key_exists($sKey, $this->aData)) {
                    if ($this->aData[$sKey] !== $sValue) {
                        $this->aOldData[$sKey] = $this->aData[$sKey];
                        $this->aData[$sKey]    = $sValue;
                    }
                } else {
                    $this->aOldData[$sKey] = '';
                    $this->aData[$sKey]    = $sValue;
                }
            }
        } else {
            if (array_key_exists($mK, $this->aData)) {
                if ($this->aData[$mK] !== $mV) {
                    $this->aOldData[$mK] = $this->aData[$mK];
                    $this->aData[$mK]    = $mV;
                }
            } else {
                $this->aOldData[$mK] = '';
                $this->aData[$mK]    = $mV;
            }
        }
    }

    /**
     * @param string $sModelName
     * @param array  $mValue
     *
     * @return $this|$this[]
     */
    public function __call($sModelName, $mValue = array())
    {
        if (substr($sModelName, 0, 5) === 'count') {
            $sModelName = substr($sModelName, 5);
            $sMethod    = 'relationCount';
        } else {
            $sMethod = 'relation';
        }
        if (empty($mValue)) {
            return $this->$sMethod($sModelName);
        } else {
            array_unshift($mValue, $sModelName);
            return call_user_func_array(array($this, $sMethod), $mValue);
        }
    }

    /**
     * @param string                                         $sModelName
     * @param array | \Slime\Component\RDBMS\DBAL\SQL_SELECT $aWhere_SQLSEL
     * @param string                                         $sOrderBy
     * @param int                                            $iLimit
     * @param int                                            $iOffset
     *
     * @return $this|$this[]
     * @throws \OutOfBoundsException
     */
    public function relation(
        $sModelName,
        $aWhere_SQLSEL = array(),
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null
    ) {
        $mResult = null;

        if (!isset($this->__M__->aRelConf[$sModelName])) {
            throw new \OutOfBoundsException("[ORM] ; Can not find relation for [$sModelName]");
        }

        $sMethod = strtolower($this->__M__->aRelConf[$sModelName]);
        if ($sMethod === 'hasone' || $sMethod === 'belongsto') {
            $mResult = $this->__Group__ === null ?
                $this->$sMethod($sModelName) :
                $this->__Group__->relation($sModelName, $this);
        } else {
            $mResult = $this->$sMethod($sModelName, $aWhere_SQLSEL, $sOrderBy, $iLimit, $iOffset);
        }

        if ($mResult === null) {
            $mResult = $this->__M__->Factory->newNull();
        }

        return $mResult;
    }

    /**
     * @param string $sModelName
     * @param array  $aWhere
     *
     * @return int
     * @throws \OutOfBoundsException
     */
    public function relationCount($sModelName, array $aWhere = array())
    {
        if (!isset($this->__M__->aRelConf[$sModelName])) {
            throw new \OutOfBoundsException("[ORM] ; Can not find relation for [$sModelName]");
        }

        $sMethod = strtolower($this->__M__->aRelConf[$sModelName]);
        if ($sMethod === 'hasone' || $sMethod === 'belongsto') {
            return null;
        }

        $sMethod .= 'Count';
        return $this->$sMethod($sModelName, $aWhere);

    }

    /**
     * @return bool
     */
    public function insert()
    {
        if (($iLastID = $this->__M__->insert($this->aData)) === false) {
            return false;
        } else {
            $this->aData[$this->__M__->sPKName] = $iLastID;
            return true;
        }
    }

    /**
     * @return bool | null | int [null:无需更新]
     */
    public function update()
    {
        $aUpdate = array_intersect_key($this->aData, $this->aOldData);
        if (empty($aUpdate)) {
            return null;
        }
        $bRS = $this->__M__->update(
            Condition::build()->add($this->__M__->sPKName, '=', $this->aData[$this->__M__->sPKName]),
            $aUpdate
        );
        if ($bRS) {
            $this->aOldData = array();
        }
        return $bRS;
    }

    /**
     * @return bool
     */
    public function reload()
    {
        $SEL = $this->__M__->SQL_SEL();
        $SEL->where(Condition::build()->add($sPKName = $this->__M__->sPKName, '=', $this->aData[$sPKName]))
            ->limit(1);
        if (($mData = $this->__M__->findCustom($SEL))===false || empty($mData[0])) {
            return false;
        }

        $this->aData = $mData[0];
        if (!empty($this->aOldData)) {
            $this->aOldData = array();
        }
        return true;
    }

    /**
     *
     * @return bool
     */
    public function delete()
    {
        return $this->__M__->delete(
            Condition::build()->add($this->__M__->sPKName, '=', $this->aData[$this->__M__->sPKName])
        );
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function hasOne($sModelName)
    {
        $M = $this->__M__->Factory->get($sModelName);
        return $M->find(Condition::build()->add($this->__M__->sFKName, '=', $this->aData[$this->__M__->sPKName]));
    }

    /**
     * @param string $sModelName
     *
     * @return Item|null
     */
    public function belongsTo($sModelName)
    {
        $M = $this->__M__->Factory->get($sModelName);
        return $M->find(Condition::build()->add($M->sPKName, '=', $this->aData[$M->sFKName]));
    }

    /**
     * @param string                        $sModel
     * @param null | Condition | SQL_SELECT $m_n_Condition_SQLSEL
     * @param string                        $sOrderBy
     * @param int                           $iLimit
     * @param int                           $iOffset
     *
     * @return Group|Item[]
     */
    public function hasMany(
        $sModel,
        $m_n_Condition_SQLSEL = null,
        $sOrderBy = null,
        $iLimit = null,
        $iOffset = null
    ) {
        $M = $this->__M__->Factory->get($sModel);
        if ($m_n_Condition_SQLSEL instanceof SQL_SELECT) {
            return $M->findMulti($m_n_Condition_SQLSEL);
        }

        $Condition = Condition::build()->add($this->__M__->sFKName, '=', $this->aData[$M->sPKName]);
        if ($m_n_Condition_SQLSEL === null) {
            $Condition->sub($m_n_Condition_SQLSEL);
        }
        return $M->findMulti($Condition, $sOrderBy, $iLimit, $iOffset);
    }

    /**
     * @param string                        $sModel
     * @param null | Condition | SQL_SELECT $m_n_Condition_SQLSEL
     *
     * @return int
     */
    public function hasManyCount($sModel, $m_n_Condition_SQLSEL = null)
    {
        $M = $this->__M__->Factory->get($sModel);
        if ($m_n_Condition_SQLSEL instanceof SQL_SELECT) {
            return $M->findCount($m_n_Condition_SQLSEL);
        }

        $Condition = Condition::build()->add($this->__M__->sFKName, '=', $this->aData[$M->sPKName]);
        if ($m_n_Condition_SQLSEL === null) {
            $Condition->sub($m_n_Condition_SQLSEL);
        }
        return $M->findCount($Condition);
    }

    /**
     * @param string                        $sModelTarget
     * @param null | Condition | SQL_SELECT $m_n_Condition_SQLSEL
     * @param string                        $nsOrderBy
     * @param int                           $niLimit
     * @param int                           $niOffset
     *
     * @return null|Group|Item[]
     */
    public function hasManyThrough(
        $sModelTarget,
        $m_n_Condition_SQLSEL = null,
        $nsOrderBy = null,
        $niLimit = null,
        $niOffset = null
    ) {
        $MTarget   = $this->__M__->Factory->get($sModelTarget);
        $MOrg      = $this->__M__;
        $sRelTName = self::getTableNameFromManyThrough($MTarget, $MOrg);

        if ($m_n_Condition_SQLSEL instanceof Condition) {
            $SQL = $MOrg->SQL_SEL()
                ->join(
                    $sRelTName,
                    Condition::build()->add(
                        "{$MTarget->sTable}.{$MTarget->sPKName}",
                        '=',
                        V::make("$sRelTName.{$MTarget->sFKName}")
                    )
                )
                ->fields("{$MTarget->sTable}.*")
                ->where($m_n_Condition_SQLSEL);
            if ($nsOrderBy !== null) {
                $SQL->orderBy($nsOrderBy);
            }
            if ($niLimit !== null) {
                $SQL->limit($niLimit);
            }
            if ($niOffset !== null) {
                $SQL->offset($niLimit);
            }

            return $MTarget->findMulti($SQL);
        } else {
            return $MTarget->findMulti($m_n_Condition_SQLSEL);
        }
    }

    /**
     * @param string                        $sModelTarget
     * @param null | Condition | SQL_SELECT $m_n_Condition_SQLSEL
     *
     * @return bool | int
     */
    public function hasManyThroughCount($sModelTarget, $m_n_Condition_SQLSEL = null)
    {
        $MTarget   = $this->__M__->Factory->get($sModelTarget);
        $MOrg      = $this->__M__;
        $sRelTName = self::getTableNameFromManyThrough($MTarget, $MOrg);

        $JoinCondition = Condition::build()->add(
            "{$MTarget->sTable}.{$MTarget->sPKName}",
            '=',
            V::make("$sRelTName.{$MTarget->sFKName}")
        );
        if ($m_n_Condition_SQLSEL instanceof Condition) {
            $SQL = $MOrg->SQL_SEL()
                ->join(
                    $sRelTName,
                    $JoinCondition
                )
                ->fields("{$MTarget->sTable}.*")
                ->where($m_n_Condition_SQLSEL);

            return $MTarget->findCount($SQL);
        } else {
            return $MTarget->findCount($m_n_Condition_SQLSEL);
        }
    }

    /**
     * @param Model $M1
     * @param Model $M2
     *
     * @return string
     */
    public static function getTableNameFromManyThrough($M1, $M2)
    {
        //@todo find in config
        $sRelatedTableName = 'rel__' . (strcmp($M1->sTable, $M2->sTable) > 0 ?
                $M2->sTable . '__' . $M1->sTable :
                $M1->sTable . '__' . $M2->sTable);
        return $sRelatedTableName;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->aData;
    }

    public function __toString()
    {
        return var_export($this->aData, true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->aData);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return isset($this->aData[$offset]) ? $this->aData[$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->aData[$offset]);
    }
}