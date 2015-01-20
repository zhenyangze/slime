<?php
namespace Slime\Component\Support;

/**
 * Class Context
 *
 * @package Slime\Component\Support
 * @author  smallslime@gmail.com
 */
class Context
{
    /** @var Context[] */
    protected static $aInst;

    /**
     * @param array $aComponentConfig
     *
     * @return Context
     */
    public static function create($aComponentConfig)
    {
        self::$aInst[] = $Inst = new static($aComponentConfig);
        return $Inst;
    }

    /**
     * @return Context
     */
    public static function inst()
    {
        return current(self::$aInst);
    }

    public static function destroy()
    {
        array_pop(self::$aInst);
        end(self::$aInst);
    }

    protected $aData = array();
    protected $aComponentConfig = array();
    protected $aCB = array();
    protected $CFG = null;

    /**
     * @param array $aComponentConfig
     */
    private function __construct(array $aComponentConfig)
    {
        $this->aComponentConfig = $aComponentConfig;
    }

    public function __get($sName)
    {
        return $this->get($sName);
    }

    public function __call($sName, $aArgv)
    {
        return $this->call($sName, $aArgv);
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function isBound($sName)
    {
        return isset($this->aData[$sName]);
    }

    /**
     * @param string $sName
     *
     * @return bool
     */
    public function isCBBound($sName)
    {
        return isset($this->aCB[$sName]);
    }

    /**
     * @param string     $sName
     * @param null|array $naParam use for replace cfg set
     * @param bool       $bDoNotThrowException
     *
     * @return null|object
     * @throws \OutOfBoundsException
     */
    public function make($sName, array $naParam = null, $bDoNotThrowException = false)
    {
        if (!isset($this->aComponentConfig[$sName])) {
            if ($bDoNotThrowException) {
                return null;
            } else {
                throw new \OutOfBoundsException("[CTX] ; [$sName] can not found in config");
            }
        }
        $aArr = $this->aComponentConfig[$sName];
        if (empty($aArr['create'])) {
            throw new \RuntimeException('[CTX] ; field [create] can not found in config');
        }

        if (!empty($aArr['parse_params']) && !empty($aArr['params'])) {
            $aArr['params'] = $this->parse($aArr['params']);
        }

        if ($naParam !== null) {
            $aArr['params'] = $naParam + $aArr['params'];
            ksort($aArr['params']);
        }
        if (is_string($aArr['create'])) {
            if (empty($aArr['params'])) {
                $Obj = new $aArr['create']();
            } else {
                $Ref = new \ReflectionClass($aArr['create']);
                $Obj = $Ref->newInstanceArgs($aArr['params']);
            }
        } else {
            $Obj = call_user_func_array(
                $aArr['create'],
                empty($aArr['params']) ? array() : $aArr['params']
            );
        }

        if (!empty($aArr['inject'])) {
            foreach ($aArr['inject'] as $sK => $sV) {
                $Obj->$sK(
                    $sV[0]===':' ?
                        $this->get(substr($sV, 1)) :
                        (
                            $sV[0]==='\\' && $sV[1]===':' ?
                                substr($sV, 1) : $sV
                        )
                );
            }
        }

        return isset($aArr['packer']) ? new Packer($Obj, (array)$aArr['packer']) : $Obj;
    }

    /**
     * @param string $sName
     *
     * @return mixed
     */
    public function get($sName)
    {
        if (!$this->isBound($sName)) {
            $this->aData[$sName] = $this->make($sName);
        }
        return $this->aData[$sName];
    }

    /**
     * @param string $sName
     *
     * @return mixed
     */
    public function getIgnore($sName)
    {
        if (!$this->isBound($sName)) {
            if (($Obj = $this->make($sName, null, true)) === null) {
                return null;
            }
            $this->aData[$sName] = $Obj;
        }
        return $this->aData[$sName];
    }

    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return mixed
     */
    public function call($sName, $aArgv = array())
    {
        if (!$this->isCBBound($sName)) {
            throw new \OutOfBoundsException("[CTX] ; CB[$sName] has not bound");
        }
        $aArgv[] = $this;
        return call_user_func_array($this->aCB[$sName], $aArgv);
    }

    /**
     * @param string $sName
     * @param array  $aArgv
     *
     * @return mixed
     */
    public function callIgnore($sName, $aArgv = array())
    {
        if ($this->isCBBound($sName)) {
            return call_user_func_array($this->aCB[$sName], $aArgv);
        } else {
            return null;
        }
    }

    /**
     * @param string $sName
     * @param mixed  $mAny
     *
     * @return $this
     */
    public function bind($sName, $mAny)
    {
        $this->aData[$sName] = $mAny;

        return $this;
    }

    /**
     * @param array $aName2Any
     *
     * @return $this
     */
    public function bindMulti(array $aName2Any)
    {
        $this->aData = array_merge($this->aData, $aName2Any);

        return $this;
    }

    /**
     * @param string $sName
     * @param mixed  $mCB
     *
     * @return $this
     */
    public function bindCB($sName, $mCB)
    {
        $this->aCB[$sName] = $mCB;

        return $this;
    }

    /**
     * @param array $aName2CB
     *
     * @return $this
     */
    public function bindCBMulti(array $aName2CB)
    {
        $this->aCB = array_merge($this->aCB, $aName2CB);

        return $this;
    }

    /**
     * @param mixed $mData
     *
     * @return mixed
     */
    public function parse($mData)
    {
        if (is_string($mData)) {
            if ($mData[0] === ':') {
                return $this->get(substr($mData, 1));
            } elseif ($mData[0] === '\\' && ($mData[1] === '\\' || $mData[1] === ':')) {
                return substr($mData, 1);
            } else {
                return $mData;
            }
        } elseif (is_array($mData)) {
            foreach ($mData as $iK => $aRow) {
                $mData[$iK] = $this->parse($aRow);
            }
            return $mData;
        } else {
            return $mData;
        }
    }
}
