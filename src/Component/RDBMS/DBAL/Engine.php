<?php
namespace Slime\Component\RDBMS\DBAL;

use Slime\Component\Event\Event;
use Slime\Component\Support\Packer;

/**
 * Class DBAL
 *
 * @package Slime\Component\RDBMS
 * @author  smallslime@gmail.com
 */
class Engine
{
    const EV_PDO_RUN_BEFORE = 'slime.component.rdbms.dbal.engine.pdo_run:before';
    const EV_PDO_RUN_AFTER = 'slime.component.rdbms.dbal.engine.pdo_run:after';
    const EV_PDO_STMT_RUN_BEFORE = 'slime.component.rdbms.dbal.engine.pdo_stmt_run:before';
    const EV_PDO_STMT_RUN_AFTER = 'slime.component.rdbms.dbal.engine.pdo_stmt_run:after';

    public static $aCBRunPDO = array('Slime\\Component\\RDBMS\\DBAL\\Engine', 'cbRunPDO');
    public static $aCBRunSTMT = array('Slime\\Component\\RDBMS\\DBAL\\Engine', 'cbRunSTMT');

    public static $__DFT_AOP_CONF__ = array(
        'query.before,exec.before,prepare.before',
        'execute.before,fetch.before,fetchAll.before,fetchColumn.before,fetchObject.before,bindValue.before'
    );

    public static function cbRunPDO(Packer $Packer, \PDO $Obj, $sMethod, $aArgv, $L)
    {
        /** @var \Slime\Component\Event\Event $Ev */
        if (($Ev = $Packer->getVar('EV')) === null) {
            $Packer->run($sMethod, $aArgv);
            return;
        }

        $STMTPacker = $Packer->getVar('STMTPacker');
        if (!$STMTPacker instanceof Packer) {
            throw new \RuntimeException('[DBAL] ; Data STMTPacker error');
        }

        $Local  = new \ArrayObject();
        $aParam = array($Obj, $sMethod, $aArgv, $Local);
        $Ev->fire(self::EV_PDO_RUN_BEFORE, $aParam);
        if (!isset($Local['__RESULT__'])) {
            $Local['__RESULT__'] = $Packer->run($sMethod, $aArgv);
        }
        $Ev->fire(self::EV_PDO_RUN_AFTER, $aParam);
        $mRS = $Local['__RESULT__'];

        $L['__RESULT__'] = ($mRS instanceof \PDOStatement) ? $STMTPacker->cloneToNewObj($mRS) : $mRS;
        $L['__STOP__']   = true;
    }

    public static function cbRunSTMT(Packer $Packer, \PDOStatement $Obj, $sMethod, $aArgv, $L)
    {
        /** @var \Slime\Component\Event\Event $Ev */
        if (($Ev = $Packer->getVar('EV')) === null) {
            $Packer->run($sMethod, $aArgv);
            return;
        }

        $Local  = new \ArrayObject();
        $aParam = array($Obj, $sMethod, $aArgv, $Local);
        $Ev->fire(self::EV_PDO_STMT_RUN_BEFORE, $aParam);
        if (!isset($Local['__RESULT__'])) {
            $Local['__RESULT__'] = $Packer->run($sMethod, $aArgv);
        }
        $Ev->fire(self::EV_PDO_STMT_RUN_AFTER, $aParam);

        $L['__RESULT__'] = $Local['__RESULT__'];
        $L['__STOP__']   = true;
    }

    protected $aInst;
    protected $aInstConf;
    protected $sDefaultInstKey;
    protected $mCBForMultiServer;

    /** @var Packer */
    protected $OBJSTMTPacker;

    /** @var SQL */
    protected $LastSQL;

    public function __get($sVar)
    {
        return $this->$sVar;
    }

    /**
     * @param array $aParams see README.md
     */
    public function __construct(array $aParams)
    {
        reset($aParams);
        $this->aInstConf       = $aParams;
        $this->sDefaultInstKey = key($aParams);
    }

    /**
     * @param string $nsMethod
     * @param array  $aArgv
     *
     * @return \PDO
     *
     * @throws \OutOfBoundsException
     */
    public function inst($nsMethod = null, array $aArgv = array())
    {
        $sK = ($mCB = $this->_getCBMasterSlave()) === null ?
            $this->sDefaultInstKey :
            call_user_func($mCB, $nsMethod, $aArgv);

        //@todo check aInst.sK 存活状态
        if (!isset($this->aInst[$sK])) {
            $aCFG      = $this->aInstConf[$sK];
            $OBJ       = new \PDO(
                $aCFG['dsn'],
                isset($aCFG['username']) ? $aCFG['username'] : null,
                isset($aCFG['password']) ? $aCFG['password'] : null,
                isset($aCFG['options']) ? $aCFG['options'] : null
            );
            $nEv       = $this->_getEvent();
            $naAopConf = $this->_getAopConf();

            if ($naAopConf !== null && $nEv !== null) {
                $STMTPacker = new Packer(null,
                    array($naAopConf[1] => array(self::$aCBRunSTMT)),
                    array('EV' => $nEv)
                );
                $OBJ        = new Packer($OBJ,
                    array($naAopConf[0] => array(self::$aCBRunPDO)),
                    array('EV' => $nEv, 'STMTPacker' => $STMTPacker)
                );
            }
            $this->aInst[$sK] = $OBJ;
        }

        return $this->aInst[$sK];
    }

    /**
     * @param SQL      $SQL
     * @param null|int $niFetchType
     *
     * @return mixed
     */
    public function Q($SQL, $niFetchType = \PDO::FETCH_ASSOC)
    {
        if ($SQL->isNeedPrepare()) {
            if (($mSTMT = $this->prepare($SQL)) === false) {
                return false;
            }
            $SQL->bind($mSTMT);
            $mSTMT->execute();
            return $niFetchType === null ? $mSTMT : $mSTMT->fetchAll($niFetchType);
        } else {
            if (($mSTMT = $this->query($SQL)) === false) {
                return $mSTMT;
            }
            return $niFetchType === null ? $mSTMT : $mSTMT->fetchAll($niFetchType);
        }
    }

    /**
     * @param SQL   $SQL
     * @param mixed $mSTMT
     *
     * @return bool|int
     */
    public function E($SQL, &$mSTMT = null)
    {
        if ($SQL->isNeedPrepare()) {
            if (($mSTMT = $this->prepare($SQL)) === false) {
                return false;
            }
            $SQL->bind($mSTMT);
            return $mSTMT->execute();
        } else {
            return $mSTMT = $this->exec($SQL);
        }
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | \PDOStatement
     */
    public function prepare($soSQL)
    {
        $this->LastSQL = $soSQL;
        return $this->inst('prepare', func_get_args())->prepare((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | \PDOStatement
     */
    public function query($soSQL)
    {
        $this->LastSQL = $soSQL;
        return $this->inst('query', func_get_args())->query((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | int
     */
    public function exec($soSQL)
    {
        $this->LastSQL = $soSQL;
        return $this->inst('exec', func_get_args())->exec((string)$soSQL);
    }

    public function getLastSQL()
    {
        $sStr = (string)$this->LastSQL;
        $aBindData = $this->LastSQL instanceof SQL && $this->LastSQL->m_n_Bind ?
            $this->LastSQL->m_n_Bind->getBindMap($this->LastSQL) : array();
        return array($sStr, $aBindData);
    }

    /** @var null|Event */
    private $_nEV = null;

    /** @var mixed */
    private $_mCBMasterSlave = null;

    /** @var null|string */
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
        $this->_mCBMasterSlave = is_object($mCB) ? array($mCB, 'run') : $mCB;
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
            $naAopConf : ($naAopConf === '__DEFAULT__' ? self::$__DFT_AOP_CONF__ : null);
    }

    /**
     * @return null|array [0:pdo_key, 1:stmt_key]
     */
    public function _getAopConf()
    {
        return $this->_naAopConf;
    }


    public function __sleep()
    {
        //@todo 缓存 packer 重新设置 Obj
        $this->aInst = array();
    }
}
