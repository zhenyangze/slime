<?php
namespace Slime\Framework;

/**
 * Class Controller_Cli
 * Slime 内置控制器基类
 *
 * @package Slime\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_ABS
{
    /**
     * @var \Slime\Component\Support\Context 上下文对象
     */
    protected $CTX;

    /**
     * @var \Slime\Component\Log\Logger 日志对象
     */
    protected $Log;

    /**
     * @var \Slime\Component\Config\IAdaptor 配置对象
     */
    protected $Config;

    /**
     * @var array 参数数组
     */
    protected $aParam;

    /**
     * @param \Slime\Component\Support\Context $CTX
     * @param array                            $aParam
     */
    public function __construct($CTX, array $aParam = array())
    {
        $this->CTX    = $CTX;
        $this->aParam = $aParam;
        $this->Log    = $CTX->Log;
        $this->Config = $CTX->Config;
    }

    /**
     * @param string $sK
     * @param mixed  $mDefault
     * @param bool   $bForce
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function getParam($sK, $mDefault = null, $bForce = false)
    {
        if (array_key_exists($sK, $this->aParam)) {
            return $this->aParam[$sK];
        } else {
            if ($bForce) {
                throw new \OutOfBoundsException("[CONTROLLER] ; Key[$sK] is not in param");
            } else {
                return $mDefault;
            }
        }
    }
}