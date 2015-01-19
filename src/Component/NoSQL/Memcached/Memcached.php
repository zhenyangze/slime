<?php
namespace Slime\Component\NoSQL\Memcached;

use Slime\Component\Event\Event;

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

    /** @var null|Event */
    protected $nEV = null;
    /**
     * @param array $aConfig
     */
    public function __construct(array $aConfig)
    {
        $this->aConfig = $aConfig;
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
        $nEv = $this->getEvent();
        if ($nEv===null) {
            return call_user_func_array(array($this->inst(), $sMethod), $aArgv);
        } else {
            $Local  = new \ArrayObject();
            $aParam = array($this, $sMethod, $aArgv, $Local);
            $nEv->fire(self::EV_CALL_BEFORE, $aParam);
            if (!isset($Local['__RESULT__'])) {
                $Local['__RESULT__'] = call_user_func_array(array($this->inst(), $sMethod), $aArgv);
            }
            $nEv->fire(self::EV_CALL_AFTER, $aParam);
            return $Local['__RESULT__'];
        }
    }

    /**
     * @param Event $Ev
     */
    public function setEvent(Event $Ev)
    {
        $this->nEV = $Ev;
    }

    /**
     * @return null|Event
     */
    public function getEvent()
    {
        return $this->nEV;
    }
}
