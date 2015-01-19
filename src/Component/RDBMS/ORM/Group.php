<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DBAL\Condition;

/**
 * Class Group
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Group implements \ArrayAccess, \Iterator, \Countable
{
    /** @var Item[] Read-only */
    public $aModelItem = array();

    private $aMapPK2PK = array();
    private $aRelation = array();
    private $aRelObj = array();

    /**
     * @param Model $Model
     */
    public function __construct($Model)
    {
        $this->Model = $Model;
    }

    public function getField($sField)
    {
        if ($sField == $this->Model->sPKName) {
            return array_keys($this->aModelItem);
        } else {
            $aRS = array();
            foreach ($this->aModelItem as $Item) {
                $aRS[] = $Item->$sField;
            }
            return $aRS;
        }
    }

    /**
     * @param string      $sModelName
     * @param Item | null $noModelItem full array if param is null
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function relation($sModelName, $noModelItem = null)
    {
        if (!isset($this->Model->aRelConf[$sModelName])) {
            throw new \OutOfBoundsException("[ORM] ; Relation model $sModelName is not exist");
        }
        $sMethod = $this->Model->aRelConf[$sModelName];
        return $this->$sMethod($sModelName, $noModelItem);
    }

    /**
     * @param string      $sModelName
     * @param Item | null $noModelItem
     *
     * @return Group | Item | null
     */
    public function hasOne($sModelName, $noModelItem = null)
    {
        if ($noModelItem === null && isset($this->aRelation[$sModelName])) {
            return $this->aRelation[$sModelName];
        }

        $sPK = $noModelItem[$this->Model->sPKName];
        if (isset($this->aRelObj[$sModelName])) {
            return isset($this->aRelObj[$sModelName][$sPK]) ? $this->aRelObj[$sModelName][$sPK] : null;
        }

        $aPK                          = array_keys($this->aModelItem);
        $Model                        = $this->Model->Factory->get($sModelName);
        $this->aRelation[$sModelName] = $Group = $Model->findMulti(
            Condition::build()->add($this->Model->sFKName, 'IN', $aPK)
        );

        if ($noModelItem === null) {
            return $Group;
        }

        $this->aRelObj[$sModelName] = array();
        $aQ                         = &$this->aRelObj[$sModelName];
        foreach ($Group as $ItemNew) {
            $sThisPK      = $this->aModelItem[$ItemNew[$this->Model->sFKName]][$this->Model->sPKName];
            $aQ[$sThisPK] = $ItemNew;
        }

        return $aQ[$sPK];
    }

    /**
     * @param string      $sModelName
     * @param Item | null $noModelItem
     *
     * @return Group | Item | null
     */
    public function belongsTo($sModelName, $noModelItem = null)
    {
        if ($noModelItem === null && isset($this->aRelation[$sModelName])) {
            return $this->aRelation[$sModelName];
        } else {
            $Model = $this->Model->Factory->get($sModelName);
            $sFK   = $noModelItem[$Model->sFKName];
            if (isset($this->aRelation[$sModelName])) {
                return isset($this->aRelation[$sModelName][$sFK]) ? $this->aRelation[$sModelName][$sFK] : null;
            }
        }

        $aFK = array();
        foreach ($this->aModelItem as $Item) {
            if ($Item[$Model->sFKName] !== null) {
                $aFK[] = $Item[$Model->sFKName];
            }
        }
        $this->aRelation[$sModelName] = $Model->findMulti(Condition::build()->add($Model->sPKName, 'IN', $aFK));
        if ($noModelItem === null) {
            return $this->aRelation[$sModelName];
        } else {
            return $this->aRelation[$sModelName][$sFK];
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return Item Can return any type.
     */
    public function current()
    {
        return $this->aModelItem[current($this->aMapPK2PK)];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->aMapPK2PK);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return current($this->aMapPK2PK);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return current($this->aMapPK2PK) !== false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->aMapPK2PK);
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
        return isset($this->aModelItem[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return Item|null Can return all value types.
     */
    public function offsetGet($offset)
    {
        if (!isset($this->aModelItem[$offset])) {
            trigger_error(sprintf('[%s] is not exist in group[%s]', $offset, (string)$this), E_USER_WARNING);
            return null;
        }
        return $this->aModelItem[$offset];
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
        $this->aMapPK2PK[$value[$this->Model->sPKName]] = $value[$this->Model->sPKName];
        $this->aModelItem[$offset]                      = $value;
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
        unset($this->aMapPK2PK[$offset], $this->aModelItem[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->aModelItem);
    }

    public function toArray($bRecursive = false)
    {
        $aArr = $this->aModelItem;
        if ($bRecursive) {
            foreach ($aArr as $sPK => $Model) {
                $aArr[$sPK] = $Model->toArray();
            }
        }
        return $aArr;
    }

    public function __toString()
    {
        $sStr = "[\n";
        foreach ($this->aModelItem as $Item) {
            $sStr .= "\t" . (string)$Item . "\n";
        }
        return ($sStr . "]\n");
    }
}