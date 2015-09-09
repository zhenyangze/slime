<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class Bind
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class Bind implements \Countable, \ArrayAccess
{
    /** @var BindItem[] */
    protected $aBind = array();
    protected $aPreData = array();

    /**
     * @param null|array $naData
     */
    public function __construct(array $naData = null)
    {
        if ($naData !== null) {
            $this->aPreData = $naData;
        }
    }

    /**
     * @param string $sK 可以传多个参数
     *
     * @return bool
     */
    public function has($sK)
    {
        foreach (func_get_args() as $sK) {
            if (!isset($this->aBind[$sK])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $sK
     * @param mixed  $mV
     *
     * @return $this
     */
    public function set($sK, $mV)
    {
        $this->aPreData[$sK] = $mV;

        return $this;
    }

    /**
     * @param array $aArr
     *
     * @return $this
     */
    public function setMulti($aArr)
    {
        $this->aPreData = array_merge($aArr, $this->aPreData);

        return $this;
    }

    /**
     * @param SQL           $SQL
     * @param \PDOStatement $STMT
     */
    public function bind($SQL, $STMT)
    {
        foreach ($SQL->getBindFields() as $sKey) {
            $mV = $this->aBind[$sKey]->mV;
            if (is_string($mV)) {
                $iType = \PDO::PARAM_STR;
            } elseif (is_int($mV)) {
                $iType = \PDO::PARAM_INT;
            } elseif (is_bool($mV)) {
                $iType = \PDO::PARAM_BOOL;
            } elseif (is_null($mV)) {
                $iType = \PDO::PARAM_NULL;
            } else {
                $iType = \PDO::PARAM_STR;
            }

            $STMT->bindValue(":$sKey", $mV, $iType);
        }
    }

    /**
     * @param SQL $SQL
     *
     * @return array
     */
    public function getBindMap($SQL)
    {
        $aMap = array();
        foreach ($SQL->getBindFields() as $sKey) {
            $aMap[$sKey] = $this->aBind[$sKey]->mV;
        }
        return $aMap;
    }

    /**
     * @param string $sK
     *
     * @return mixed
     */
    public function get($sK)
    {
        return $this->offsetGet($sK)->mV;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     *       </p>
     *       <p>
     *       The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->aBind);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->aBind[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return BindItem
     */
    public function offsetGet($offset)
    {
        if (!isset($this->aBind[$offset])) {
            if (!isset($this->aPreData[$offset])) {
                throw new \OutOfBoundsException("[DBAL] ; Key[$offset] has not been bind before");
            }
            $this->aBind[$offset] = $this->aPreData[$offset];
        }

        return $this->aBind[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->aBind[$offset]);
    }
}
