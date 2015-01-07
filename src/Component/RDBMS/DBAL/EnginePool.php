<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class EnginePool
 *
 * @package Slime\Component\RDBMS\DBAL
 * @author  smallslime@gmail.com
 */
class EnginePool
{
    protected $aConf;
    protected $aEngine;
    protected $nEV;

    /**
     * @param array $aConf see README.md
     * @param null|\Slime\Component\Event\Event $nEV
     */
    public function __construct(array $aConf, $nEV = null)
    {
        $this->aConf = $aConf;
        $this->nEV   = $nEV;
    }

    public function get($sK)
    {
        if (!isset($this->aEngine[$sK])) {
            if (!isset($this->aConf['__DB__'][$sK])) {
                throw new \OutOfRangeException("[DBAL] : Database config [$sK] is not exist");
            }
            $this->aEngine[$sK] = new Engine(
                $this->aConf['__DB__'][$sK],
                isset($this->aConf['__CB_MultiServer__']) ? $this->aConf['__CB_MultiServer__'] : null,
                isset($this->aConf['__AOP_PDO__']) ? $this->aConf['__AOP_PDO__'] : null,
                isset($this->aConf['__AOP_STMT__']) ? $this->aConf['__AOP_STMT__'] : null,
                $this->nEV
            );
        }
        return $this->aEngine[$sK];
    }
}
