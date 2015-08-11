<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\Event\Event;
use Slime\Component\RDBMS\DBAL\EnginePool;
use Slime\Component\Support\Context;

/**
 * Class Factory
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Factory
{
    protected static $aDefaultSetting = array(
        'db'         => 'default',
        'model_pre'  => '\\',
        'item_pre'   => '\\',
        'model_base' => 'Slime\\Component\\RDBMS\\ORM\\Model',
        'item_base'  => 'Slime\\Component\\RDBMS\\ORM\\Item'
    );

    protected $aConf;
    protected $aDFT;
    /** @var Model[] */
    protected $aM = array();

    /**
     * @param Item | CItem | Group | null $mData
     *
     * @return bool
     */
    public static function isEmpty($mData)
    {
        if ($mData === null ||
            $mData instanceof CItem ||
            ($mData instanceof Group && $mData->count() == 0)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $aConf model conf
     */
    public function __construct(array $aConf)
    {
        $aConf['__DEFAULT__'] = empty($aConf['__DEFAULT__']) ?
            self::$aDefaultSetting :
            array_merge(self::$aDefaultSetting, $aConf['__DEFAULT__']);
        $this->aDFT           = $aConf['__DEFAULT__'];
        $this->aConf          = $aConf['__MODEL__'];
    }

    /**
     * @param string $sM Like M_User() first 2 letter can be anything
     * @param array  $aArg
     *
     * @return Model
     */
    public function __call($sM, $aArg)
    {
        return $this->get(substr($sM, 2));
    }

    /**
     * @param string $sM
     *
     * @return Model
     * @throws \OutOfBoundsException
     */
    public function get($sM)
    {
        if (isset($this->aM[$sM])) {
            return $this->aM[$sM];
        }

        $aConf = isset($this->aConf[$sM]) ? $this->aConf[$sM] : array();
        if (isset($aConf['model'])) {
            $sMClass    = "{$this->aDFT['model_pre']}{$aConf['model']}";
            $sItemClass = "{$this->aDFT['item_pre']}{$aConf['model']}";
        } else {
            $sMClass    = "{$this->aDFT['model_pre']}{$sM}";
            $sItemClass = "{$this->aDFT['item_pre']}{$sM}";
            if (!empty($this->aDFT['auto_create']) && !class_exists($sMClass, true)) {
                $sMClass    = $this->aDFT['model_base'];
                $sItemClass = $this->aDFT['item_base'];
            }
        }

        $Obj = new $sMClass(
            $this, $sM, $sItemClass, $this->_getEnginePool(),
            $aConf,
            $this->aDFT
        );
        if (!empty($aConf['inject']) && is_array($aConf['inject'])) {
            $this->_getCTX()->inject($Obj, $aConf['inject']);
        }

        return $this->aM[$sM] = $Obj;
    }

    /**
     * @param $sVar
     *
     * @return mixed
     */
    public function __get($sVar)
    {
        return $this->$sVar;
    }

    protected $bCMode = false;
    protected $bCMode_Tmp = false;

    /**
     * @param bool $b
     */
    public function changeCMode_Tmp($b)
    {
        if ($this->bCMode != $b) {
            $this->bCMode_Tmp = $this->bCMode;
            $this->bCMode     = (bool)$b;
        }
    }

    public function resetCMode()
    {
        if ($this->bCMode_Tmp !== null) {
            $this->bCMode     = $this->bCMode_Tmp;
            $this->bCMode_Tmp = null;
        }
    }

    /**
     * @param bool $b
     */
    public function changeCMode($b)
    {
        $this->bCMode = (bool)$b;
    }

    /**
     * @return bool
     */
    public function isCMode()
    {
        return $this->bCMode;
    }

    public function newNull()
    {
        return $this->bCMode ? new CItem() : null;
    }

    /** @var null|EnginePool */
    private $_nEnginePool = null;

    /** @var null|Event */
    private $_nEV = null;

    /** @var null|Context */
    private $_nCTX = null;

    /**
     * @param Event $EV
     */
    public function _setEvent(Event $EV)
    {
        $this->_nEV = $EV;
        $this->_getEnginePool()->_setEvent($EV);
    }

    /**
     * @return null|Event
     */
    public function _getEvent()
    {
        return $this->_nEV;
    }

    /**
     * @param EnginePool $EnginePool
     */
    public function _setEnginePool(EnginePool $EnginePool)
    {
        $this->_nEnginePool = $EnginePool;
    }

    /**
     * @return EnginePool
     *
     * @throws \RuntimeException
     */
    public function _getEnginePool()
    {
        if ($this->_nEnginePool === null) {
            throw new \RuntimeException('[ORM] ; EnginePool is not set before');
        }
        return $this->_nEnginePool;
    }

    /**
     * @return null|Context
     */
    public function _getCTX()
    {
        return $this->_nCTX;
    }

    /**
     * @param Context $CTX
     */
    public function _setCTX(Context $CTX)
    {
        $this->_nCTX = $CTX;
    }
}