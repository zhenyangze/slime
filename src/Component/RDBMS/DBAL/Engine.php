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

    protected $aInst;
    protected $aInstConf;
    protected $sDefaultInstKey;
    protected $mCBForMultiServer;
    protected $sAopPDOKey;
    protected $sAopPDOStmtKey;
    protected $bMultiInst;

    /** @var Packer */
    protected $OBJPackerForSTMT;

    /** @var null|Event */
    protected $nEV;

    public function __get($sVar)
    {
        return $this->$sVar;
    }

    /**
     * @param array                             $aParams           see README.md
     * @param null|MasterSlaveMode              $mCBForMultiServer callback for select instance config from sql
     * @param null|string                       $nsAopPDOKey       default is 'query.before,exec.before,prepare.before'
     * @param null|string                       $nsAopPDOStmtKey   default is
     */
    public function __construct(
        array $aParams,
        $mCBForMultiServer = null,
        $nsAopPDOKey = null,
        $nsAopPDOStmtKey = null
    ) {
        reset($aParams);
        $this->aInstConf         = $aParams;
        $this->sDefaultInstKey   = key($aParams);
        $this->mCBForMultiServer = $mCBForMultiServer;
        $this->sAopPDOKey        = $nsAopPDOKey === null ? 'query.before,exec.before,prepare.before' : $nsAopPDOKey;
        $this->sAopPDOStmtKey    = $nsAopPDOStmtKey === null ?
            'execute.before,fetch.before,fetchAll.before,fetchColumn.before,fetchObject.before,bindValue.before' :
            $nsAopPDOStmtKey;

        $this->bMultiInst = count($this->aInstConf) > 1 && $this->mCBForMultiServer !== null;
    }

    /**
     * @param string $sMethod
     * @param array  $aArgv
     *
     * @return \PDO
     *
     * @throws \OutOfBoundsException
     */
    public function inst($sMethod, array $aArgv = array())
    {
        reset($this->aInstConf);
        $sDftK = key($this->aInstConf);
        $sK    = $this->bMultiInst ? call_user_func(array($this->mCBForMultiServer), $sMethod, $aArgv) : $sDftK;

        //@todo check aInst.sK 存活状态
        if (!isset($this->aInst[$sK])) {
            $aCFG = isset($this->aInstConf[$sK]) ? $this->aInstConf[$sK] : $this->aInstConf[$sDftK];
            $OBJ  = new \PDO($aCFG['dsn'], $aCFG['username'], $aCFG['password'], $aCFG['options']);
            $nEv = $this->getEvent();
            if ($this->sAopPDOKey !== '' && $nEv !== null) {

                $OBJPackerForSTMT = new Packer(null,
                    array($this->sAopPDOStmtKey => array(array('Slime\\Component\\RDBMS\\DBAL\\Engine', 'cbRunSTMT'))),
                    array('EV' => $nEv)
                );

                $OBJ = new Packer($OBJ,
                    array($this->sAopPDOKey => array(array('Slime\\Component\\RDBMS\\DBAL\\Engine', 'cbRunPDO'))),
                    array('EV' => $nEv, 'PackerForSTMT' => $OBJPackerForSTMT)
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
        return $this->inst(__METHOD__, func_get_args())->prepare((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | \PDOStatement
     */
    public function query($soSQL)
    {
        return $this->inst(__METHOD__, func_get_args())->query((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | int
     */
    public function exec($soSQL)
    {
        return $this->inst(__METHOD__, func_get_args())->exec((string)$soSQL);
    }

    public static function cbRunPDO(Packer $Packer, \PDO $Obj, $sMethod, $aArgv, $L)
    {
        /** @var \Slime\Component\Event\Event $Ev */
        if (($Ev = $Packer->getVar('EV')) === null) {
            return;
        }

        $PackerForSTMT = $Packer->getVar('PackerForSTMT');
        if (!$PackerForSTMT instanceof Packer) {
            throw new \RuntimeException('[DBAL] ; Data PackerForSTMT error');
        }

        $Local  = new \ArrayObject();
        $aParam = array($Obj, $sMethod, $aArgv, $Local);
        $Ev->fire(self::EV_PDO_RUN_BEFORE, $aParam);
        if (!isset($Local['__RESULT__'])) {
            $Local['__RESULT__'] = $Packer->run($sMethod, $aArgv);
        }
        $Ev->fire(self::EV_PDO_RUN_AFTER, $aParam);
        $mRS = $Local['__RESULT__'];

        $L['__RESULT__'] = ($mRS instanceof \PDOStatement) ? $PackerForSTMT->cloneToNewObj($mRS) : $mRS;
        $L['__STOP__']   = true;
    }

    public static function cbRunSTMT(Packer $Packer, \PDOStatement $Obj, $sMethod, $aArgv, $L)
    {
        /** @var \Slime\Component\Event\Event $Ev */
        if (($Ev = $Packer->getVar('EV')) === null) {
            return;
        }

        $Local  = new \ArrayObject();
        $aParam = array($Obj, $sMethod, $aArgv, $Local);
        $Ev->fire(self::EV_PDO_STMT_RUN_BEFORE, $aParam);
        if (!isset($Local['__RESULT__'])) {
            $Local['__RESULT__'] = call_user_func_array(array($Obj, $sMethod), $aArgv);
        }
        $Ev->fire(self::EV_PDO_STMT_RUN_AFTER, $aParam);

        $L['__RESULT__'] = $Local['__RESULT__'];
        $L['__STOP__']   = true;
    }

    /**
     * @param Event $nEV
     */
    public function setEvent(Event $nEV)
    {
        $this->nEV = $nEV;
    }

    /**
     * @return null|Event
     */
    public function getEvent()
    {
        return $this->nEV;
    }
}
