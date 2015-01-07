<?php
namespace Slime\Component\Support;

/**
 * Class Packer
 *
 * @package Slime\Component\Context
 * @author  smallslime@gmail.com
 */
class Packer
{
    const BEFORE = 0;
    const AFTER = 1;

    protected $aAOPCallBack = array();
    protected $aAOPMatchCallBack = array();
    protected $Obj = null;
    protected $aDataMap = array();

    /**
     * @param object $mObj         obj to be packed
     * @param array  $aAOPCallBack ['execute.before,query.after' => [function(){xxx}, 'cbFunc1'], ...]
     * @param array $aDataMap
     */
    public function __construct($mObj, array $aAOPCallBack = array(), array $aDataMap = array())
    {
        $this->Obj = $mObj;
        if (!empty($aAOPCallBack)) {
            foreach ($aAOPCallBack as $sExplain => $aCB) {
                foreach ($aCB as $mCB) {
                    $this->addCB($sExplain, $mCB);
                }
            }
        }

        $this->aDataMap = $aDataMap;
    }

    /**
     * @param $sVar
     *
     * @return mixed
     */
    public function getVar($sVar)
    {
        return isset($this->aDataMap[$sVar]) ? $this->aDataMap[$sVar] : null;
    }

    /**
     * do not use!!!
     * @param object $Obj
     */
    public function __setObjDanger($Obj)
    {
        $this->Obj = $Obj;
    }

    /**
     * @param object $Obj
     *
     * @return Packer
     */
    public function cloneToNewObj($Obj)
    {
        $O = clone $this;
        $O->__setObjDanger($Obj);
        return $O;
    }

    /**
     * @param string $sExplain 'execute.before,query.after'
     * @param mixed  $mCB
     *
     * @return $this
     */
    public function addCB($sExplain, $mCB)
    {
        $aArr = explode(',', $sExplain);
        foreach ($aArr as $sKey) {
            $sKey = trim($sKey);
            if ($sKey{0} == '/' || $sKey{0} == '#') {
                $iPos  = strrpos($sKey, '.');
                $sPos  = substr($sKey, $iPos + 1);
                $sPreg = substr($sKey, 0, $iPos);
                if ($sPos === 'before') {
                    $this->aAOPMatchCallBack[self::BEFORE][$sPreg] = $mCB;
                } elseif ($sPos === 'after') {
                    $this->aAOPMatchCallBack[self::AFTER][$sPreg] = $mCB;
                } else {
                    throw new \RuntimeException("[PACKER] ; method is not support[$sPos]");
                }
            } else {
                list($sMethod, $sPos) = array_replace(array('', ''), explode('.', $sKey, 2));
                if ($sPos === 'before') {
                    $this->aAOPCallBack[$sMethod][self::BEFORE][] = $mCB;
                } elseif ($sPos === 'after') {
                    $this->aAOPCallBack[$sMethod][self::AFTER][] = $mCB;
                } else {
                    throw new \RuntimeException("[PACKER] ; method is not support[$sPos]");
                }
            }
        }

        return $this;
    }

    //@todo  get  set inject
    public function __get($sKey)
    {
        return $this->Obj->$sKey;
    }

    public function __set($sKey, $mValue)
    {
        $this->Obj->sKey = $mValue;
    }

    public function __call($sMethod, $aArgv)
    {
        # return if no inject
        if (empty($this->aAOPCallBack[$sMethod]) && empty($this->aAOPMatchCallBack)) {
            return $this->run($sMethod, $aArgv);
        }

        $Local = new \ArrayObject();
        if (isset($this->aAOPCallBack[$sMethod])) {
            # inject
            $aCB = $this->aAOPCallBack[$sMethod];

            if (!empty($aCB[self::BEFORE])) {
                foreach ($aCB[self::BEFORE] as $mCB) {
                    if (call_user_func($mCB, $this, $this->Obj, $sMethod, $aArgv,
                            $Local) === false || !empty($Local['__STOP__'])
                    ) {
                        break;
                    }
                }
            }

            if (empty($Local['__STOP__'])) {
                $Local['__RESULT__'] = call_user_func_array(array($this->Obj, $sMethod), $aArgv);
            }

            if (empty($Local['__STOP__']) && !empty($aCB[self::AFTER])) {
                foreach ($aCB[self::AFTER] as $mCB) {
                    if (call_user_func($mCB, $this, $this->Obj, $sMethod, $aArgv,
                            $Local) === false || !empty($Local['__STOP__'])
                    ) {
                        break;
                    }
                }
            }
        } else {
            # inject by RE
            if (!empty($this->aAOPMatchCallBack[self::BEFORE])) {
                foreach ($this->aAOPMatchCallBack[self::BEFORE] as $sPreg => $mCB) {
                    if (preg_match($sPreg, $sMethod) !== false &&
                        call_user_func($mCB, $this->Obj, $sMethod, $aArgv, $Local) === false &&
                        !empty($Local['__STOP__'])
                    ) {
                        break;
                    }
                }
            }
            if (empty($Local['__STOP__'])) {
                $Local['__RESULT__'] = $this->run($sMethod, $aArgv);
            }
            if (!empty($this->aAOPMatchCallBack[self::AFTER])) {
                foreach ($this->aAOPMatchCallBack[self::AFTER] as $sPreg => $mCB) {
                    if (preg_match($sPreg, $sMethod) !== false &&
                        call_user_func($mCB, $this->Obj, $sMethod, $aArgv, $Local) === false &&
                        !empty($Local['__STOP__'])
                    ) {
                        break;
                    }
                }
            }
        }

        return $Local['__RESULT__'];
    }

    public function run($sMethod, $aArgv)
    {
        return empty($aArgv) ? $this->Obj->$sMethod() : call_user_func_array(array($this->Obj, $sMethod), $aArgv);
    }
}