<?php
namespace Slime\Component\Support;

/**
 * Class XML
 *
 * @package Slime\Component\Support
 * @author  smallslime@gmail.com
 *
 * @example :
 *          $aArr = array(
 *              'books' => array(
 *                  'book' => 'The book name', //easy way if no index duplicate
 *                  array(
 *                      'book' => 'Common english book',
 *                      '__attr__' => array('important' => '0', 'has_read' => '1'),
 *                  ),
 *                  array(
 *                      'book' => XML::Str('有中文需CDATA'),
 *                      '__attr__' => array('important' => '1', 'has_read' => '1'),
 *                  ),
 *              ),
 *              'buyer' => 'smallslime'
 *          );
 *
 *          $sXML = EasyXML::Array2XML($aArr);
 *          var_dump($sXML);
 *          var_dump(EasyXML::XML2Array($sXML));
 *          var_dump(EasyXML::XML2Array($sXML, true));
 *          var_dump(EasyXML::XML2Array($sXML, true, 'root', '1.0', 'gbk', function($sStr){return iconv('utf-8', 'gbk',
 *          $sStr);}));
 */
class XML
{
    public static function Str($sString, $bCDATA = true, $mCB = null)
    {
        return new XmlString($sString, $bCDATA, $mCB);
    }

    /**
     * @param string       $sXML
     * @param bool         $bStrAsObj
     * @param string       $sRoot
     * @param string       $sVersion
     * @param string       $sCharset
     * @param mixed | null $nmCBIconv
     *
     * @return array|string
     */
    public static function XML2Array(
        $sXML,
        $bStrAsObj = false,
        $sRoot = 'root',
        $sVersion = '1.0',
        $sCharset = 'utf-8',
        $nmCBIconv = null
    ) {
        $DOM = new \DOMDocument($sVersion, $sCharset);
        $DOM->loadXML($sXML);
        $Root = $DOM->getElementsByTagName($sRoot)->item(0);
        $aArr = self::ParseXML($Root, $bStrAsObj, $nmCBIconv);
        return $aArr[$sRoot];
    }

    /**
     * @param \DOMNode     $Node
     * @param bool         $bStrAsObj
     * @param mixed | null $nmCBIconv
     *
     * @return array|string
     */
    public static function ParseXML($Node, $bStrAsObj, $nmCBIconv = null)
    {
        if ($Node instanceof \DOMCdataSection) {
            $sStr = $bStrAsObj ? self::Str("$Node->textContent") : $Node->textContent;
            return $nmCBIconv === null ? $sStr : call_user_func($nmCBIconv, $sStr);
        } elseif ($Node instanceof \DOMText) {
            return $nmCBIconv === null ? $Node->textContent : call_user_func($nmCBIconv, $Node->textContent);
        } else {
            $sIndex = $Node->nodeName;
            $mValue = null;
            if ($Node->hasAttributes()) {
                foreach ($Node->attributes as $sK => $sV) {
                    $sV = (string)$sV->value;
                    if ($nmCBIconv !== null) {
                        $sK = call_user_func($nmCBIconv, $sK);
                        $sV = call_user_func($nmCBIconv, $sV);
                    }
                    $aArr['__attr__'][$sK] = $sV;
                }
            }
            if ($Node->hasChildNodes()) {
                $ChildNodes = $Node->childNodes;
                $iL         = $ChildNodes->length;
                for ($i = 0; $i < $iL; $i++) {
                    $mRS = self::ParseXML($ChildNodes->item($i), $bStrAsObj, $nmCBIconv);
                    if (is_string($mRS)) {
                        if ($iL !== 1) {
                            trigger_error(
                                'XML format strange that a node has text node and others. function will ignore others',
                                E_USER_WARNING
                            );
                        }
                        $mValue = $mRS;
                        break;
                    } else {
                        $mValue[] = $mRS;
                    }
                }
            }
            return array($sIndex => $mValue);
        }
    }

    /**
     * @param array        $aData
     * @param string       $sRoot
     * @param string       $sVersion
     * @param string       $sCharset
     * @param mixed | null $nmCBIconv
     *
     * @return \DOMDocument
     */
    public static function Array2XML(
        array $aData,
        $sRoot = 'root',
        $sVersion = '1.0',
        $sCharset = 'utf-8',
        $nmCBIconv = null
    ) {
        $DOM  = new \DOMDocument($sVersion, $sCharset);
        $Root = $DOM->createElement($sRoot);
        self::BuildXML($aData, $Root, $DOM, $nmCBIconv);
        $DOM->appendChild($Root);
        return $DOM->saveXML();
    }

    /**
     * @param array        $mData
     * @param \DOMNode     $ParentDOM
     * @param \DOMDocument $DOMDocument
     * @param mixed | null $nmCBIconv
     */
    public static function BuildXML($mData, \DOMNode $ParentDOM, \DOMDocument $DOMDocument, $nmCBIconv = null)
    {
        if (is_array($mData)) {
            foreach ($mData as $mK => $mV) {
                if (is_int($mK)) {
                    if (isset($mV['__attr__'])) {
                        $aAttr = $mV['__attr__'];
                        unset($mV['__attr__']);
                    }

                    if (count($mV) !== 1) {
                        trigger_error(
                            'Error format when build array to xml. You must define one and only one k=>v in array value when your current index is int',
                            E_USER_WARNING
                        );
                    }
                    reset($mV);
                    $mK = key($mV);
                    $mV = current($mV);
                }

                $CurDOM = $DOMDocument->createElement($mK);

                if (isset($aAttr) && is_array($aAttr)) {
                    foreach ($aAttr as $sK => $sV) {
                        if ($nmCBIconv !== null) {
                            $sK = call_user_func($nmCBIconv, $sK);
                            $sV = call_user_func($nmCBIconv, $sV);
                        }
                        $DomAttr        = $DOMDocument->createAttribute($sK);
                        $DomAttr->value = (string)$sV;
                        $CurDOM->appendChild($DomAttr);
                    }
                }

                self::BuildXML($mV, $CurDOM, $DOMDocument, $nmCBIconv);
                $ParentDOM->appendChild($CurDOM);
            }
        } else {
            $bCreateCDATA = false;
            $sStr         = (string)$mData;
            if ($mData instanceof XmlString && $mData->isCDATA()) {
                $bCreateCDATA = true;
            }

            if ($nmCBIconv !== null) {
                $sStr = call_user_func($nmCBIconv, $sStr);
            }

            $ParentDOM->appendChild(
                $bCreateCDATA ?
                    $DOMDocument->createCDATASection($sStr) :
                    $DOMDocument->createTextNode($sStr)
            );
        }
    }
}

class XmlString
{
    protected $sStr;
    protected $bCDATA;

    public function __construct($sStr, $bCDATA)
    {
        $this->sStr   = $sStr;
        $this->bCDATA = $bCDATA;
    }

    public function isCDATA()
    {
        return $this->bCDATA;
    }

    public function __toString()
    {
        return $this->sStr;
    }
}