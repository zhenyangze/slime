<?php
namespace Slime\Component\NoSQL\Redis;

/**
 * Class Redis
 *
 * @package Slime\Component\NoSQL\Redis
 * @author  smallslime@gmail.com
 */
class Redis
{
    const EV_CALL_BEFORE = 'slime.component.redis.call_before';
    const EV_CALL_AFTER = 'slime.component.redis.call_after';

    /**
     * @var \Redis[]
     */
    private $aInst;

    /**
     * @var array
     */
    protected $aInstConf;
    protected $mCB;
    protected $nEV = null;

    /**
     * @param array                             $aConfig
     * @param mixed                             $mCB
     * @param null|\Slime\Component\Event\Event $nEV
     */
    public function __construct(array $aConfig, $mCB = null, $nEV = null)
    {
        $this->aInstConf = $aConfig;
        $this->mCB       = $mCB;
        $this->nEV       = $nEV;
    }

    public function __call($sMethod, $aArgv)
    {
        if ($this->nEV) {
            $Local  = new \ArrayObject();
            $aParam = array($this, $sMethod, $aArgv, $Local);
            $this->nEV->fire(self::EV_CALL_BEFORE, $aParam);
            if (!isset($Local['__RESULT__'])) {
                $Local['__RESULT__'] = call_user_func_array(array($this->inst($sMethod), $sMethod), $aArgv);
            }
            $this->nEV->fire(self::EV_CALL_AFTER, $aParam);
            return $Local['__RESULT__'];
        } else {
            return call_user_func_array(array($this->inst($sMethod), $sMethod), $aArgv);
        }
    }

    public function inst($nsMethod = null)
    {
        reset($this->aInstConf);
        $sDftK = key($this->aInstConf);
        if ($nsMethod !== null && $this->mCB !== null) {
            $sK = call_user_func($this->mCB, $nsMethod);
        } else {
            $sK = $sDftK;
        }

        if (!isset($this->aInst[$sK])) {
            $aCFG = isset($this->aInstConf[$sK]) ? $this->aInstConf[$sK] : $this->aInstConf[$sDftK];
            if (!isset($aCFG['server']) && !isset($aCFG['servers'])) {
                throw new \RuntimeException("[REDIS] ; Configure error : field [servers] can not be found");
            }
            $aS = isset($aCFG['server']) ? $aCFG['server'] : $aCFG['servers'];
            if (is_array($aS)) {
                $OBJ = new \RedisArray($aS, isset($aCFG['setting']) ? $aCFG['setting'] : array());
            } else {
                $OBJ = new \Redis();
                if (empty($aCFG['pconnect'])) {
                    $OBJ->connect($aS,
                        isset($aCFG['port']) ? $aCFG['port'] : 6379,
                        isset($aCFG['timeout']) ? $aCFG['timeout'] : 0.0
                    );
                } else {
                    $OBJ->pconnect($aS,
                        isset($aCFG['port']) ? $aCFG['port'] : 6379,
                        isset($aCFG['timeout']) ? $aCFG['timeout'] : 0.0
                    );
                }
            }
            if (isset($aCFG['db'])) {
                $OBJ->select($aCFG['db']);
            }

            $this->aInst[$sK] = $OBJ;
        }

        return $this->aInst[$sK];
    }
}
