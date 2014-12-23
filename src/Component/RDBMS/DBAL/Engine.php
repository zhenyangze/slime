<?php
namespace Slime\Component\RDBMS\DBAL;

use Slime\Component\Support\Packer;

/**
 * Class DBAL
 *
 * @package Slime\Component\RDBMS
 * @author  smallslime@gmail.com
 */
class Engine
{
    protected $aInst;
    protected $aInstConf;
    protected $sDefaultInstKey;
    protected $mCB;
    protected $naAopConf;

    /**
     * @param array $aParams   see README.md
     * @param mixed $mCB       callback for select instance config from sql
     * @param mixed $naAopConf Conf of pdo aop
     */
    public function __construct(array $aParams, $mCB = null, $naAopConf = null)
    {
        reset($aParams);
        $this->aInstConf       = $aParams;
        $this->sDefaultInstKey = key($aParams);
        $this->mCB             = $mCB;
        $this->naAopConf       = $naAopConf;
    }

    /**
     * @param SQL | string | null $nsoSQL
     *
     * @return \PDO
     *
     * @throws \OutOfBoundsException
     */
    public function inst($nsoSQL = null)
    {
        reset($this->aInstConf);
        $sDftK = key($this->aInstConf);
        if ($nsoSQL !== null && $this->mCB !== null) {
            $sK = call_user_func($this->mCB, $nsoSQL);
        } else {
            $sK = $sDftK;
        }

        //@todo check aInst.sK 存活状态
        if (!isset($this->aInst[$sK])) {
            $aCFG = isset($this->aInstConf[$sK]) ? $this->aInstConf[$sK] : $this->aInstConf[$sDftK];
            $OBJ  = new \PDO($aCFG['dsn'], $aCFG['username'], $aCFG['password'], $aCFG['options']);
            if ($this->naAopConf !== null) {
                $OBJ = new Packer($OBJ, $this->naAopConf);
            }
            $this->aInst[$sK] = $OBJ;
        }

        return $this->aInst[$sK];
    }

    /**
     * @param SQL       $SQL
     * @param null|Bind $m_n_Bind
     * @param null|int  $niFetchType
     *
     * @return mixed
     */
    public function Q($SQL, $m_n_Bind = null, $niFetchType = \PDO::FETCH_ASSOC)
    {
        if ($m_n_Bind !== null) {
            $SQL->setBind($m_n_Bind);
        };
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
     * @param SQL       $SQL
     * @param null|Bind $m_n_Bind
     * @param mixed     $mSTMT
     *
     * @return bool|int
     */
    public function E($SQL, $m_n_Bind = null, &$mSTMT = null)
    {
        if ($m_n_Bind !== null) {
            $SQL->setBind($m_n_Bind);
        };
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
        return $this->inst($soSQL)->prepare((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | \PDOStatement
     */
    public function query($soSQL)
    {
        return $this->inst($soSQL)->query((string)$soSQL);
    }

    /**
     * @param string | SQL $soSQL
     *
     * @return bool | int
     */
    public function exec($soSQL)
    {
        return $this->inst($soSQL)->exec((string)$soSQL);
    }
}
