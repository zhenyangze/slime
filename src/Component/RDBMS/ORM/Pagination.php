<?php
namespace Slime\Component\RDBMS\ORM;

use Slime\Component\Http\REQ;
use Slime\Component\Http\RESP;
use Slime\Component\RDBMS\DBAL\Condition;
use Slime\Component\RDBMS\DBAL\SQL_SELECT;
use Slime\Component\Support\Url;

/**
 * Class Pagination
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Pagination
{
    public static $aCBRender = array('Slime\\Component\\RDBMS\\ORM\\Pagination', 'cbRender');
    public static $aCBError = array('Slime\\Component\\RDBMS\\ORM\\Pagination', 'cbError');

    /**
     * @var int
     */
    public $iNumPerPage;

    /**
     * @var string
     */
    public $sPageVar;

    /**
     * @var mixed
     */
    public $mCBRender;

    /**
     * @var mixed
     */
    public $mCBError;

    /**
     * @param int    $iNumPerPage
     * @param string $sPageVar
     * @param mixed  $mCBRender
     * @param mixed  $mCBError
     */
    public function __construct(
        $iNumPerPage,
        $sPageVar = 'page',
        $mCBRender = null,
        $mCBError = null
    ) {
        $this->iNumPerPage = $iNumPerPage;
        $this->sPageVar    = $sPageVar;
        $this->mCBRender   = $mCBRender === null ? self::$aCBRender : $mCBRender;
        $this->mCBError    = $mCBError === null ? self::$aCBError : $mCBError;
    }

    /**
     * @param Model            $Model
     * @param null|SQL_SELECT|Condition $nConditionSQLSEL
     * @param mixed       $mCBForCount
     * @param mixed     $mCBForList
     *
     * @return bool|array [page_string, List_Group, total_item, page_count, current_page, number_per_page]
     */
    public function generate($Model, $nConditionSQLSEL = null, $mCBForCount = null, $mCBForList = null)
    {
        $REQ  = $this->_getREQ();
        $RESP = $this->_getRESP();
        $Url  = $this->_getURL();

        # number per page
        $iNumPerPage = max(1, $this->iNumPerPage);

        # current page
        $iCurrentPage = ($sPageNum = $REQ->getG($this->sPageVar)) === null ? 1 : (int)$sPageNum;

        if ($nConditionSQLSEL instanceof SQL_SELECT) {
            $OrgSEL = $nConditionSQLSEL;
        } else {
            $OrgSEL = $Model->SQL_SEL();
            if ($nConditionSQLSEL instanceof Condition) {
                $OrgSEL->where($nConditionSQLSEL);
            }
        }
        $SQL_SEL_Total = clone $OrgSEL;

        # get total
        $iItem = $Model->findCount($SQL_SEL_Total, $mCBForCount);

        # get pagination data
        $RS = self::doPagination($iItem, $iNumPerPage, $iCurrentPage);
        if (!empty($RS['__error__'])) {
            call_user_func($this->mCBError, $RS, $this->sPageVar, $REQ, $Url, $RESP);
            return false;
        }
        $sPage = call_user_func($this->mCBRender, $RS, $this->sPageVar, $REQ, $Url, $RESP);

        # get list data
        $SQL_SEL_List = clone $OrgSEL;
        $SQL_SEL_List->limit($iNumPerPage)->offset(($iCurrentPage - 1) * $iNumPerPage);
        $List = $Model->findMulti($SQL_SEL_List, null, null, null, $mCBForList);

        # result
        return array(
            'org_result'   => $RS,
            'pagination'   => $sPage,
            'group'        => $List,
            'item_count'   => $iItem,
            'page_count'   => $RS['page_count'],
            'current_page' => $iCurrentPage,
            'num_pre_page' => $iNumPerPage
        );
    }

    /**
     * @param \ArrayObject $RS
     * @param string       $sPageVar
     * @param REQ          $REQ
     * @param Url          $URL
     * @param RESP         $RESP
     */
    public static function cbError($RS, $sPageVar, REQ $REQ, Url $URL, RESP $RESP)
    {
        if (!empty($RS['__error__'])) {
            switch ($RS['__error__']) {
                case 1:
                    $RESP->setRedirect($URL->getNewUrl(array($sPageVar => 1)));
                    return;
                case 2:
                    $RESP->setRedirect($URL->getNewUrl(array($sPageVar => $RS['__total__'])));
                    return;
                default:
                    return;
            }
        }
    }

    /**
     * @param \ArrayObject $RS
     * @param string       $sPageVar
     * @param REQ          $REQ
     * @param Url          $URL
     * @param RESP         $RESP
     *
     * @return string
     */
    public static function cbRender(\ArrayObject $RS, $sPageVar, REQ $REQ, URL $URL, RESP $RESP)
    {
        if (empty($RS['list'])) {
            return '';
        }

        $sPage       = '<ul class="pagination">';
        $RS['first'] = 1;
        foreach (
            array(
                'first'      => '首页',
                'pre'        => '&lt;&lt;',
                'list'       => $RS['list'],
                'next'       => '&gt;&gt',
                'page_count' => '末页'
            ) as $sK => $sV
        ) {
            if ($sK === 'list') {
                foreach ($RS[$sK] as $iPage) {
                    $sPage .= $iPage < 0 ?
                        sprintf('<li><span>%s</span></li>', 0 - $iPage) :
                        sprintf(
                            '<li><a href="%s">%s</a></li>',
                            $URL->getNewUrl(array($sPageVar => $iPage)),
                            $iPage
                        );
                }
            } else {
                $iPage = $RS[$sK];
                $sPage .= $iPage <= 0 ?
                    "<li><span>{$sV}</span></li>" :
                    sprintf(
                        '<li><a href="%s">%s</a></li>',
                        $URL->getNewUrl(array($sPageVar => $iPage)),
                        $sV
                    );
            }
        }
        $sPage .= '</ul>';

        return $sPage;
    }

    /**
     * @param int      $iTotalItem
     * @param int      $iNumPerPage
     * @param int      $iCurrentPage
     * @param int      $iDisplayBefore
     * @param int|null $iDisplayAfter
     *
     * @return \ArrayObject [pre:int list:int[] next:int total:int] If pre||list[]||next < 0, it means abs(value) is
     *                      current page
     */
    public static function doPagination(
        $iTotalItem,
        $iNumPerPage,
        $iCurrentPage,
        $iDisplayBefore = 3,
        $iDisplayAfter = null
    ) {
        if ($iCurrentPage < 1) {
            return new \ArrayObject(array('__error__' => 1, '__current__' => $iCurrentPage));
        }

        if ($iTotalItem == 0) {
            return new \ArrayObject(array('__error__' => -1));
        }

        if (empty($iDisplayAfter)) {
            $iDisplayAfter = $iDisplayBefore;
        }

        $iTotalPage = (int)ceil($iTotalItem / $iNumPerPage);
        if ($iCurrentPage > $iTotalPage) {
            return new \ArrayObject(
                array(
                    '__error__'   => 2,
                    '__total__'   => $iTotalPage,
                    '__current__' => $iCurrentPage
                )
            );
        }

        # count start
        $iStart = $iCurrentPage - $iDisplayBefore;
        $iEnd   = $iCurrentPage + $iDisplayAfter;

        $iFixStart = max(1, $iStart - max(0, $iCurrentPage + $iDisplayAfter - $iTotalPage));
        $iFixEnd   = min($iTotalPage, $iEnd + (0 - min(0, $iCurrentPage - $iDisplayBefore - 1)));

        # build array
        $aResult = array();
        for ($i = $iFixStart; $i <= $iFixEnd; $i++) {
            if ($i == $iCurrentPage) {
                $aResult[] = 0 - $i;
            } else {
                $aResult[] = $i;
            }
        }

        # build data
        $iPre  = $iCurrentPage - 1;
        $iNext = $iCurrentPage + 1;
        if ($iCurrentPage == 1) {
            $iPre = -1;
        }
        if ($iCurrentPage == $iTotalPage) {
            $iNext = 0 - $iTotalPage;
        }

        return new \ArrayObject(
            array('__error__' => 0, 'pre' => $iPre, 'list' => $aResult, 'next' => $iNext, 'page_count' => $iTotalPage)
        );
    }


    /**
     * @var null|REQ
     */
    private $_nREQ;

    /**
     * @var null|Url
     */
    private $_nURL;

    /**
     * @var null|RESP
     */
    private $_nRESP;

    /**
     * @param REQ $REQ
     */
    public function _setREQ(REQ $REQ)
    {
        $this->_nREQ = $REQ;
    }

    /**
     * @return REQ
     *
     * @throws \RuntimeException
     */
    public function _getREQ()
    {
        if ($this->_nREQ === null) {
            throw new \RuntimeException('[Pagination] ; REQ is not set before');
        }
        return $this->_nREQ;
    }

    /**
     * @param RESP $RESP
     */
    public function _setRESP(RESP $RESP)
    {
        $this->_nRESP = $RESP;
    }

    /**
     * @return RESP
     *
     * @throws \RuntimeException
     */
    public function _getRESP()
    {
        if ($this->_nRESP === null) {
            throw new \RuntimeException('[Pagination] ; RESP is not set before');
        }
        return $this->_nRESP;
    }

    /**
     * @return Url
     */
    public function _getURL()
    {
        if ($this->_nURL === null) {
            $this->_nURL = new Url($this->_getREQ()->getUrl());
        }
        return $this->_nURL;
    }


    public function __sleep()
    {
        $this->_nREQ  = null;
        $this->_nURL  = null;
        $this->_nRESP = null;
    }
}