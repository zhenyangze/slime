<?php
namespace Slime\Component\RDBMS\DBAL;

use Slime\Component\Support\Context;
use Slime\Component\Support\Packer;
use Slime\Component\Log\Logger;

class Ext
{
    protected static $m_n_sForceSlaveMaster = null;

    public static function setMaterForce()
    {
        self::$m_n_sForceSlaveMaster = 'master';
    }

    public static function setSlaveForce()
    {
        self::$m_n_sForceSlaveMaster = 'slave';
    }

    public static function resetMasterSlaveMode()
    {
        self::$m_n_sForceSlaveMaster = null;
    }

    public static function autoMasterSlave($m_s_SQL)
    {
        if (self::$m_n_sForceSlaveMaster !== null) {
            return self::$m_n_sForceSlaveMaster;
        }

        if ($m_s_SQL instanceof SQL) {
            return $m_s_SQL instanceof SQL_SELECT ? 'slave' : 'master';
        } elseif (is_string($m_s_SQL) && ($m_s_SQL[0]==='@')) {
            return substr($m_s_SQL, 1);
        } else {
            return strtoupper(substr(trim($m_s_SQL), 0, 6) === 'SELECT') ? 'slave' : 'master';
        }
    }

    public static function cbRunPDO(Packer $Packer, $Obj, $sMethod, $aArgv, $Local)
    {
        $Local['__STOP__'] = true;

        /** @var \Slime\Component\Log\Logger $Log */
        $Log = Context::inst()->Log;

        /** @var \Slime\Component\Support\Packer $Obj */
        $Log->info("[SQL] ; $sMethod begin ; " . (isset($aArgv[0]) ? $aArgv[0] : ''));
        $fT1 = microtime(true);
        $mRS = $Packer->run($sMethod, $aArgv);
        $Log->info("[SQL] ; $sMethod finish ; cost:" . round(microtime(true) - $fT1, 4));

        if ($mRS instanceof \PDOStatement) {
            $Packer_STMT         = new Packer($mRS,
                array(
                    'execute.before,fetch.before,fetchAll.before,fetchColumn.before,fetchObject.before' => array(
                        array('\\Slime\\Component\\RDBMS\\DBAL\\Ext', 'cbRunSTMT')
                    ),
                    'bindValue.before'                                                                  => array(
                        array('\\Slime\\Component\\RDBMS\\DBAL\\Ext', 'cbBindLog')
                    )
                )
            );
            $Local['__RESULT__'] = $Packer_STMT;
        } else {
            $Local['__RESULT__'] = $mRS;
        }
    }

    public static function cbRunSTMT(Packer $Packer, $Obj, $sMethod, $aArgv, $Local)
    {
        $Local['__STOP__'] = true;

        /** @var \Slime\Component\Log\Logger $Log */
        $Log = Context::inst()->Log;

        $Log->info("[SQL] ; $sMethod begin ; " . json_encode($aArgv, JSON_UNESCAPED_UNICODE));
        $fT1 = microtime(true);
        $mRS = $Packer->run($sMethod, $aArgv);
        $Log->info(
            sprintf('[SQL] ; %s finish ; cost:%s ; result:%s',
                $sMethod,
                round(microtime(true) - $fT1, 4),
                $mRS === false ? 'failed' : 'ok'
            )
        );

        $Local['__RESULT__'] = $mRS;
    }

    public static function cbBindLog(Packer $Packer, $Obj, $sMethod, $aArgv, $Local)
    {
        /** @var \Slime\Component\Log\Logger $Log */
        $Log = Context::inst()->Log;

        $Log->info("[SQL] ; bind ; " . json_encode($aArgv, JSON_UNESCAPED_UNICODE));
    }
}