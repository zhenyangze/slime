<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class EnginePool
 *
 * @package Slime\Component\RDBMS\DBAL
 * @authro  smallslime@gmail.com
 */
class EnginePool
{
    protected $aConf;
    protected $aEngine;

    /**
     * @param array $aConf see README.md
     */
    public function __construct(array $aConf)
    {
        $this->aConf = $aConf;
    }

    public function get($sK)
    {
        if (!isset($this->aEngine[$sK])) {
            if (!isset($this->aConf['__DB__'][$sK])) {
                throw new \OutOfRangeException("[DBAL] : Database config [$sK] is not exist");
            }
            $this->aEngine[$sK] = new Engine(
                $this->aConf['__DB__'][$sK],
                $this->aConf['__CB__'],
                $this->aConf['__AOP__']
            );
        }
        return $this->aEngine[$sK];
    }
}
