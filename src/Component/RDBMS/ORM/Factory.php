<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\RDBMS\DBAL\EnginePool;

/**
 * Class Factory
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Factory
{
    /** @var \Slime\Component\RDBMS\DBAL\EnginePool */
    protected $EnginePool;
    protected $aConf;
    protected $aDFT;

    /** @var Model[] */
    protected $aM = array();

    protected static $aDefaultSetting = array(
        'db'         => 'default',
        'model_pre'  => '\\',
        'item_pre'   => '\\',
        'model_base' => '\\Slime\\Component\\RDBMS\\ORM\\Model',
        'item_base'  => '\\Slime\\Component\\RDBMS\\ORM\\Item'
    );

    /**
     * @param array $aDBConf db conf
     * @param array $aMConf  model conf  ['__MODEL__' => [], '__DEFAULT__' => []]
     *
     * @return Factory
     */
    public static function createFromConfig(array $aDBConf, array $aMConf)
    {
        $EnginePool            = new EnginePool($aDBConf);
        $aMConf['__DEFAULT__'] = empty($aMConf['__DEFAULT__']) ?
            self::$aDefaultSetting :
            array_merge(self::$aDefaultSetting, $aMConf['__DEFAULT__']);

        return new self($EnginePool, empty($aMConf['__MODEL__']) ? array() : $aMConf['__MODEL__'],
            $aMConf['__DEFAULT__']);
    }

    /**
     * @param \Slime\Component\RDBMS\DBAL\EnginePool $EnginePool
     * @param array                                  $aConf    model conf
     * @param array                                  $aDefault model default setting
     */
    public function __construct($EnginePool, array $aConf, array $aDefault)
    {
        $this->EnginePool = $EnginePool;
        $this->aConf      = $aConf;
        $this->aDFT       = $aDefault;
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
     * @throws \OutOfRangeException
     */
    public function get($sM)
    {
        if (isset($this->aM[$sM])) {
            goto END;
        }

        if (!isset($this->aConf[$sM])) {
            if (!empty($this->aDFT['create_direct'])) {
                $sMClass       = "{$this->aDFT['model_pre']}{$sM}";
                $sItemClass    = "{$this->aDFT['item_pre']}{$sM}";
                $this->aM[$sM] = new $sMClass($this, $sM, $sItemClass, $this->EnginePool, $this->aDFT['db'], null);
                goto END;
            }
            if (empty($this->aDFT['auto_create'])) {
                throw new \DomainException("[ORM] ; Model conf[$sM] is not exists");
            }
        } else {
            $naConf = $this->aConf[$sM];
        }

        if (!isset($naConf['model'])) {
            $sMClass    = $this->aDFT['model_base'];
            $sItemClass = $this->aDFT['item_base'];
        } else {
            $sMClass    = "{$this->aDFT['model_pre']}{$naConf['model']}";
            $sItemClass = "{$this->aDFT['item_pre']}{$naConf['model']}";
        }
        $this->aM[$sM] = new $sMClass(
            $this, $sM, $sItemClass, $this->EnginePool,
            isset($naConf[$sM]['db']) ? $naConf[$sM]['db'] : $this->aDFT['db'],
            $naConf
        );

        END:
        return $this->aM[$sM];
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

    protected static $bCMode = false;
    protected static $bCMode_Tmp = false;

    /**
     * @param bool $b
     */
    public static function changeCMode_Tmp($b)
    {
        if (self::$bCMode !== $b) {
            self::$bCMode_Tmp = self::$bCMode;
            self::$bCMode     = $b;
        }
    }

    public static function resetCMode()
    {
        if (self::$bCMode_Tmp !== null) {
            self::$bCMode     = self::$bCMode_Tmp;
            self::$bCMode_Tmp = null;
        }
    }

    /**
     * @param bool $b
     */
    public static function changeCMode($b)
    {
        self::$bCMode = $b;
    }

    /**
     * @return bool
     */
    public static function isCMode()
    {
        return (bool)self::$bCMode;
    }

    public static function newNull()
    {
        return self::isCMode() ? new CItem() : null;
    }

    /**
     * @param Item | CItem | Group | null $mData
     *
     * @return bool
     */
    public static function mEmpty($mData)
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
}