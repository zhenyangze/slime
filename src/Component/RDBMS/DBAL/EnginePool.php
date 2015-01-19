<?php
namespace Slime\Component\RDBMS\DBAL;

use Slime\Component\Event\Event;

/**
 * Class EnginePool
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class EnginePool
{
    protected $aConf;

    /** @var Engine[] */
    protected $aEngine;

    /** @var null|Event */
    protected $nEV;

    /**
     * @param array $aConf see README.md
     */
    public function __construct(array $aConf)
    {
        $this->aConf = $aConf;
    }

    /**
     * @param string $sK
     *
     * @return Engine
     *
     * @throws \OutOfBoundsException
     */
    public function get($sK)
    {
        if (!isset($this->aEngine[$sK])) {
            if (!isset($this->aConf['__DB__'][$sK])) {
                throw new \OutOfBoundsException("[DBAL] ; Database config [$sK] is not exist");
            }
            $Obj = new Engine(
                $this->aConf['__DB__'][$sK],
                isset($this->aConf['__CB_MultiServer__']) ? $this->aConf['__CB_MultiServer__'] : null,
                isset($this->aConf['__AOP_PDO__']) ? $this->aConf['__AOP_PDO__'] : null,
                isset($this->aConf['__AOP_STMT__']) ? $this->aConf['__AOP_STMT__'] : null
            );
            if (($Ev = $this->getEvent()) !== null) {
                $Obj->setEvent($Ev);
            }

            $this->aEngine[$sK] = $Obj;
        }
        return $this->aEngine[$sK];
    }

    /**
     * @param Event $EV
     */
    public function setEvent(Event $EV)
    {
        $this->nEV = $EV;
    }

    /**
     * @return null|Event
     */
    public function getEvent()
    {
        return $this->nEV;
    }
}
