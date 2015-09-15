<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DBAL\Condition;
use Slime\Component\RDBMS\DBAL\V;
use Slime\Component\Support\CompatibleEmpty;

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
     * @return Item|Item[]|Group|null
     */
    public function __call($sModelName, $mValue = array())
    {
        return $this->relation(
            $sModelName,
            isset($mValue[0]) ? $mValue[0] : null,
            substr($sModelName, 0, 5) === 'count'
        );
    }

    /**
     * @param string $sModelName
     * @param mixed  $mCBBeforeQ
     * @param bool   $bCount
     *
     * @return Item|Item[]|Group|null
     * @throws \OutOfBoundsException
     */
    public function relation(
        $sModelName,
        $mCBBeforeQ = null,
        $bCount = false
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
            $mResult = $this->$sMethod($sModelName, $mCBBeforeQ, $bCount);
        }

        if ($mResult === null) {
            $mResult = new CompatibleEmpty();
        }

        return $mResult;
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
     * @return bool|null|int [null:无需更新]
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
        if (($mData = $this->__M__->findMultiArray($SEL)) === false || empty($mData[0])) {
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
     * @param mixed  $mCBBeforeQ
     *
     * @return Item|null
     */
    public function hasOne($sModelName, $mCBBeforeQ = null)
    {
        $M = $this->__M__->Factory->get($sModelName);
        return $M->find(
            Condition::build()->add($this->__M__->getFKName($M), '=', $this->aData[$this->__M__->sPKName]),
            $mCBBeforeQ
        );
    }

    /**
     * @param string $sModelName
     * @param mixed  $mCBBeforeQ
     *
     * @return Item|null
     */
    public function belongsTo($sModelName, $mCBBeforeQ = null)
    {
        $M = $this->__M__->Factory->get($sModelName);
        return $M->find(
            Condition::build()->add($M->sPKName, '=', $this->aData[$M->getFKName($this->__M__)]),
            $mCBBeforeQ
        );
    }

    /**
     * @param string $sModel
     * @param mixed  $mCBBeforeQ
     * @param bool   $bCount
     *
     * @return Group|Item[]
     */
    public function hasMany(
        $sModel,
        $mCBBeforeQ = null,
        $bCount = false
    ) {
        $M         = $this->__M__->Factory->get($sModel);
        $Condition = Condition::build()->add($this->__M__->getFKName($M), '=', $this->aData[$M->sPKName]);
        return $bCount ?
            $M->findCount($Condition, $mCBBeforeQ) :
            $M->findMulti($Condition, null, null, null, $mCBBeforeQ);
    }

    /**
     * @param string $sModelTarget
     * @param mixed  $mCBBeforeQ
     * @param bool   $bCount
     *
     * @return Group|Item[]
     */
    public function hasManyThrough($sModelTarget, $mCBBeforeQ = null, $bCount = false)
    {
        $MTarget   = $this->__M__->Factory->get($sModelTarget);
        $MOrg      = $this->__M__;
        $sRelTName = $MOrg->getThroughTable($MTarget);

        $SQL = $MTarget->SQL_SEL()
            ->join(
                $sRelTName,
                Condition::build()->add(
                    "{$MTarget->sTable}.{$MTarget->sPKName}",
                    '=',
                    V::make("$sRelTName.{$MTarget->getFKName($MOrg)}")
                )
            )
            ->join(
                $MOrg->sTable,
                Condition::build()->add(
                    "{$MOrg->sTable}.{$MOrg->sPKName}",
                    '=',
                    V::make("$sRelTName.{$MOrg->getFKName($MTarget)}")
                )
            )
            ->fields("{$MTarget->sTable}.*");
        $SQL->where(
            Condition::build()->add("{$MOrg->sTable}.{$MOrg->sPKName}", '=', $this->{$MOrg->sPKName})
        );

        return $bCount ?
            $MTarget->findCount($SQL, $mCBBeforeQ) :
            $MTarget->findMulti($SQL, null, null, null, $mCBBeforeQ);
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
        return (string)var_export($this->aData, true);
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

    public function isEmpty()
    {
        return false;
    }
}