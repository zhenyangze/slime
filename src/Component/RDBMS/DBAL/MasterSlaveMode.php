<?php
namespace Slime\Component\RDBMS\DBAL;

class MasterSlaveMode
{
    protected static $aQuery = array(
        'prepare' => 1,
        'query'   => 1,
        'exec'    => 1,
    );

    private $nsTmpThisTime;
    private $nsTmp;

    public function __construct()
    {
        $this->sLast = 'master';
    }

    public function setMaster($bOnlyThisTime = true)
    {
        if ($bOnlyThisTime) {
            $this->nsTmpThisTime = 'master';
        } else {
            $this->nsTmp = 'master';
        }
    }

    public function setSlave($bOnlyThisTime = true)
    {
        if ($bOnlyThisTime) {
            $this->nsTmpThisTime = 'master';
        } else {
            $this->nsTmp = 'master';
        }
    }

    public function resetAutoMode()
    {
        $this->nsTmpThisTime = null;
        $this->nsTmp         = null;;
    }

    public function run($sMethod, $aArgv)
    {
        if ($this->nsTmpThisTime !== null) {
            $sRS                 = $this->nsTmpThisTime;
            $this->nsTmpThisTime = null;
            return $sRS;
        }

        if ($this->nsTmp !== null) {
            $this->sLast = $this->nsTmp;
            return $this->nsTmp;
        }

        if (isset(self::$aQuery[$sMethod])) {
            $m_s_SQL = $aArgv[0];

            if ($m_s_SQL instanceof SQL) {
                $this->sLast = $m_s_SQL instanceof SQL_SELECT ? 'slave' : 'master';
            } else {
                $this->sLast = strtoupper(substr(trim((string)$m_s_SQL), 0, 6) === 'SELECT') ? 'slave' : 'master';
            }
        }

        return $this->sLast;
    }
}