<?php
namespace Slime\Component\NoSQL\Memcached;

/**
 * Class Redis
 *
 * @package Slime\Component\NoSQL\Memcached
 * @author  smallslime@gmail.com
 */
class Memcached
{
    const EV_CALL_BEFORE = 'slime.component.nosql.memcached.call_before';
    const EV_CALL_AFTER = 'slime.component.nosql.memcached.call_after';
    /**
     * @var \Memcached
     */
    protected $Inst = null;
    protected $nEV = null;

    /**
     * @param array                             $aConfig
     * @param null|\Slime\Component\Event\Event $nEV
     */
    public function __construct(array $aConfig, $nEV = null)
    {
        $this->aConfig = $aConfig;
        $this->nEV     = $nEV;
    }

    /**
     * @return \Memcached
     */
    public function inst()
    {
        if ($this->Inst === null) {
            $this->Inst = isset($this->aConfig['persistent_id']) ?
                new \Memcached($this->aConfig['persistent_id']) : new \Memcached();
            $this->Inst->addServers($this->aConfig['servers']);
        }
        return $this->Inst;
    }

    public function __call($sMethod, $aArgv)
    {
        if ($this->nEV) {
            $Local  = new \ArrayObject();
            $aParam = array($this, $sMethod, $aArgv, $Local);
            $this->nEV->fire(self::EV_CALL_BEFORE, $aParam);
            if (!isset($Local['__RESULT__'])) {
                $Local['__RESULT__'] = call_user_func_array(array($this->inst(), $sMethod), $aArgv);
            }
            $this->nEV->fire(self::EV_CALL_AFTER, $aParam);
            return $Local['__RESULT__'];
        } else {
            return call_user_func_array(array($this->inst(), $sMethod), $aArgv);
        }
    }
}
