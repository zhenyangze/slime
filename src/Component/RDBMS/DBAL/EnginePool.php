<?php
namespace Slime\Component\RDBMS\DBAL;

use Slime\Component\Event\Event;

/**
 * Class EnginePool
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class EnginePool
{
    protected $aConf;

    /** @var Engine[] */
    protected $aEngine;

    /**
     * @param array $aConf see README.md
     */
    public function __construct(array $aConf)
    {
        $this->aConf = $aConf;
    }

    /**
     * @param string $sK
     *
     * @return Engine
     *
     * @throws \OutOfBoundsException
     */
    public function get($sK)
    {
        if (!isset($this->aEngine[$sK])) {
            if (!isset($this->aConf[$sK])) {
                throw new \OutOfBoundsException("[DBAL] ; Database config [$sK] is not exist");
            }
            $Obj = new Engine($this->aConf[$sK]);
            if (($Ev = $this->_getEvent()) !== null) {
                $Obj->_setEvent($Ev);
            }
            if (($mCB = $this->_getCBMasterSlave()) !== null) {
                $Obj->_setCBMasterSlave($mCB);
            }
            if (($naAOP = $this->_getAopConf()) !== null) {
                $Obj->_setAopConf($naAOP);
            }

            $this->aEngine[$sK] = $Obj;
        }
        return $this->aEngine[$sK];
    }


    /** @var null|Event */
    private $_nEV = null;

    /** @var mixed */
    private $_mCBMasterSlave = null;

    /** @var null|array */
    private $_naAopConf = null;

    /**
     * @param Event $nEV
     */
    public function _setEvent(Event $nEV)
    {
        $this->_nEV = $nEV;
    }

    /**
     * @return null|Event
     */
    public function _getEvent()
    {
        return $this->_nEV;
    }

    /**
     * @param mixed $mCB
     */
    public function _setCBMasterSlave($mCB)
    {
        $this->_mCBMasterSlave = $mCB;
    }

    /**
     * @return mixed
     */
    public function _getCBMasterSlave()
    {
        return $this->_mCBMasterSlave;
    }

    /**
     * @param null|array|string:__DEFAULT__ $naAopConf
     */
    public function _setAopConf($naAopConf)
    {
        $this->_naAopConf = (is_array($naAopConf) && !empty($naAopConf[0]) && !empty($naAopConf[1])) ?
            $naAopConf : ($naAopConf === '__DEFAULT__' ? Engine::$__DFT_AOP_CONF__ : null);
    }

    /**
     * @return null|array [0:pdo_key, 1:stmt_key]
     */
    public function _getAopConf()
    {
        return $this->_naAopConf;
    }
}
