<?php
namespace Slime\Framework;

use Slime\Component\Support\Context;

/**
 * Class Controller_Page
 * Slime 内置 Page 控制器基类
 *
 * @package Slime\Framework
 * @author  smallslime@gmail.com
 */
abstract class Controller_Page extends Controller_ABS
{
    # render type
    const RENDER_NONE = -1;
    const RENDER_AUTO = 0;
    const RENDER_PAGE = 1;
    const RENDER_JUMP = 2;

    /** @var \Slime\Component\Http\REQ */
    protected $REQ;
    /** @var \Slime\Component\Http\RESP */
    protected $RESP;

    # for render
    protected $sTPL = null;
    protected $aData = array();

    # for jump
    protected $sJumpUrl = null;
    protected $iJumpCode = null;

    # render/jump logic if get
    protected $iRender = self::RENDER_AUTO;

    /**
     * @param Context $CTX
     * @param array   $aParam
     */
    public function __construct($CTX, array $aParam = array())
    {
        parent::__construct($CTX, $aParam);

        $this->REQ  = $CTX->REQ;
        $this->RESP = $CTX->RESP;
    }

    /**
     * 主逻辑完成后运行
     */
    public function __after__()
    {
        if ($this->isNoneRender()) {
            return;
        } elseif ($this->isJumpRender()) {
            $sJump = $this->sJumpUrl === null ? $this->REQ->getHeader('Referer') : $this->sJumpUrl;
            $this->RESP->setRedirect($sJump === null ? '/' : $sJump, $this->iJumpCode);
        } else {
            if ($this->RESP->getHeader('Content-Type') === null) {
                $this->RESP->addHeader('Content-Type', 'text/html; charset=utf-8');
            }
            $this->RESP->setBody(
                $this->CTX->View
                    ->assignMulti($this->aData)
                    ->setTpl($this->sTPL === null ? $this->getDefaultTPL() : $this->sTPL)
                    ->renderAsResult()
            );
        }
    }

    /**
     * @return string
     */
    protected function getDefaultTPL()
    {
        return sprintf(
            '%s-%s.%s',
            str_replace($this->aParam['__SETTING__']['controller_pre'], '', $this->aParam['__CONTROLLER__']),
            str_replace($this->aParam['__SETTING__']['action_pre'], '', $this->aParam['__ACTION__']),
            $this->aParam['__EXT__'] === null ? 'php' : $this->aParam['__EXT__']
        );
    }

    public function setAsNoneRender()
    {
        $this->iRender = self::RENDER_NONE;
    }

    /**
     * @return bool
     */
    protected function isNoneRender()
    {
        return $this->iRender === self::RENDER_NONE;
    }

    /**
     * @param null|string $nsTPL
     */
    public function setAsPageRender($nsTPL = null)
    {
        $this->iRender = self::RENDER_PAGE;
        if ($nsTPL !== null) {
            $this->sTPL = $nsTPL;
        }
    }

    /**
     * @return bool
     */
    protected function isPageRender()
    {
        return $this->iRender === self::RENDER_PAGE ||
        ($this->iRender === self::RENDER_AUTO && $this->REQ->getMethod() === 'GET');
    }

    /**
     * @param null|string $nsJumpUrl
     * @param null|int    $niJumpCode
     */
    public function setAsJumpRender($nsJumpUrl = null, $niJumpCode = null)
    {
        $this->iRender = self::RENDER_JUMP;
        if ($nsJumpUrl !== null) {
            $this->sJumpUrl = $nsJumpUrl;
            if ($niJumpCode !== null) {
                $this->iJumpCode = $niJumpCode;
            }
        }
    }

    /**
     * @return bool
     */
    protected function isJumpRender()
    {
        return $this->iRender === self::RENDER_JUMP ||
        ($this->iRender === self::RENDER_AUTO && $this->REQ->getMethod() !== 'GET');
    }
}