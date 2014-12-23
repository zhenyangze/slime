<?php
namespace Slime\Component\RDBMS\ORM;

/**
 * Class Pagination
 *
 * @package Slime\Component\RDBMS\ORM
 * @author  smallslime@gmail.com
 */
class Pagination
{
    /**
     * @param \Slime\Component\Http\REQ $HttpREQ
     * @param int                       $iNumPerPage
     * @param null|mixed                $m_PageVar_PageVarCB
     * @param null|mixed                $mCBRender
     */
    public function __construct(
        $HttpREQ,
        $iNumPerPage,
        $m_PageVar_PageVarCB = 'page',
        $mCBRender = null
    ) {
        $this->HttpRequest         = $HttpREQ;
        $this->iNumPerPage         = $iNumPerPage;
        $this->m_PageVar_PageVarCB = $m_PageVar_PageVarCB;
        $this->mCBRender           = $mCBRender === null ? array(
            '\\Slime\\Component\\RDBMS\\ORM\\Pagination',
            'renderDefault'
        ) : $mCBRender;
    }

    /**
     * @param \Slime\Component\RDBMS\ORM\Model            $Model
     * @param null|\Slime\Component\RDBMS\DBAL\SQL_SELECT $nSEL
     *
     * @return array [page_string, List_Group, total_item, total_page, current_page, number_per_page]
     */
    public function generate($Model, $nSEL = null)
    {
        return $this->_generate(
            array($Model, 'findCount'),
            array($Model, 'findMulti'),
            $nSEL === null ? $Model->SQL_SEL() : $nSEL
        );
    }

    /**
     * @param callable                               $mCBCount
     * @param callable                               $mCBList
     * @param \Slime\Component\RDBMS\DBAL\SQL_SELECT $SQL_SEL
     *
     * @return array [page_string, List_Group, total_item, total_page, current_page, number_per_page]
     */
    public function _generate(
        $mCBCount,
        $mCBList,
        $SQL_SEL
    ) {
        # number per page
        $iNumPerPage = max(1, $this->iNumPerPage);

        # current page
        $iCurrentPage = is_string($this->m_PageVar_PageVarCB) ?
            max(1, (int)$this->HttpRequest->getG($this->m_PageVar_PageVarCB)) :
            (int)call_user_func($this->m_PageVar_PageVarCB);

        $SQL_SEL_Total = clone $SQL_SEL;
        # get total
        $iToTal = call_user_func($mCBCount, $SQL_SEL_Total);

        # get pagination data
        $aResult = self::doPagination($iToTal, $iNumPerPage, $iCurrentPage);

        $SQL_SEL->limit($iNumPerPage)->offset(($iCurrentPage - 1) * $iNumPerPage);

        # get list data
        $List = call_user_func($mCBList, $SQL_SEL);

        $sPage = $this->mCBRender === null ? $aResult : call_user_func($this->mCBRender, $this->HttpRequest, $aResult);

        # result
        return array($sPage, $List, $iToTal, $aResult['total_page'], $iCurrentPage, $iNumPerPage);
    }

    /**
     * @param \Slime\Component\Http\REQ $REQ
     * @param array                     $aResult
     *
     * @return string
     */
    public static function renderDefault($REQ, $aResult)
    {
        if (empty($aResult['list'])) {
            return '';
        }

        $sURI             = strstr($REQ->getUrl(), '?', true);
        $aGet             = $REQ->getG();
        $sPage            = '<ul class="pagination">';
        $aResult['first'] = 1;
        foreach (
            array(
                'first'      => '首页',
                'pre'        => '&lt;&lt;',
                'list'       => $aResult['list'],
                'next'       => '&gt;&gt',
                'total_page' => '末页'
            ) as $sK => $sV
        ) {
            if ($sK === 'list') {
                foreach ($aResult[$sK] as $iPage) {
                    $aGet['page'] = $iPage;
                    $sPage .= $iPage < 0 ?
                        sprintf('<li><span>%s</span></li>', 0 - $iPage) :
                        sprintf(
                            '<li><a href="%s?%s">%s</a></li>',
                            $sURI,
                            http_build_query($aGet),
                            $iPage
                        );
                }
            } else {
                $iPage        = $aResult[$sK];
                $aGet['page'] = abs($iPage);
                $sPage .= $iPage <= 0 ?
                    "<li><span>{$sV}</span></li>" :
                    sprintf(
                        '<li><a href="%s?%s">%s</a></li>',
                        $sURI,
                        http_build_query($aGet),
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function doPagination(
        $iTotalItem,
        $iNumPerPage,
        $iCurrentPage,
        $iDisplayBefore = 3,
        $iDisplayAfter = null
    ) {
        if ($iCurrentPage < 1) {
            throw new \LogicException('[PAG] ; Offset can not be less than 1');
        }
        if ($iTotalItem == 0) {
            return array();
        }

        if (empty($iDisplayAfter)) {
            $iDisplayAfter = $iDisplayBefore;
        }

        $iTotalPage = (int)ceil($iTotalItem / $iNumPerPage);
        if ($iCurrentPage > $iTotalPage) {
            throw new \LogicException('[PAG] ; Offset can not be more than total page');
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
            array('pre' => $iPre, 'list' => $aResult, 'next' => $iNext, 'total_page' => $iTotalPage)
        );
    }
}