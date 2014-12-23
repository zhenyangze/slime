<?php
namespace Slime\Component\RDBMS\DBAL;

/**
 * Class Condition
 *
 * @package Slime\Component\RDBMS\DBAL
 *
 * @property-read string $sRel
 * @property-read array  $aData
 */
class Condition
{
    protected static $sDefaultRel = 'AND';
    protected static $m_n_sTmpDefaultRel = null;

    public static function setRelDefault($sRel = 'AND')
    {
        self::$sDefaultRel = $sRel;
    }

    public static function setRelDefault_Tmp($sRel)
    {
        self::$m_n_sTmpDefaultRel = self::$sDefaultRel;
        self::$sDefaultRel        = $sRel;
    }

    public static function resetRelDefault()
    {
        if (self::$m_n_sTmpDefaultRel !== null) {
            self::$sDefaultRel        = self::$m_n_sTmpDefaultRel;
            self::$m_n_sTmpDefaultRel = null;
        }
    }


    public static function build()
    {
        return new self(self::$sDefaultRel);
    }

    public static function buildAnd()
    {
        return new self('AND');
    }

    public static function buildOr()
    {
        return new self('OR');
    }

    public function __construct($sRel)
    {
        $this->sRel  = $sRel;
        $this->aData = array();
    }

    /**
     * @param string $sK  key
     * @param string $mOP if param count == 3 : sOP means '=', 'LIKE' etc.. ; if param count == 2 : sOP means value
     * @param mixed  $mV  if param count == 3 : mV means value ; if param count === 2 : you needn't set mV
     *
     * @return $this
     */
    public function add($sK, $mOP, $mV = null)
    {
        $this->aData[] = $mV === null ? array($sK, '=', $mOP) : array($sK, (string)$mOP, $mV);

        return $this;
    }

    /**
     * @param array $aCondition see params of method set
     *
     * @return $this
     */
    public function addMulti($aCondition)
    {
        foreach ($aCondition as $aRow) {
            $this->aData[] = !isset($aRow[2]) ? array($aRow[0], '=', $aRow[1]) : array(
                $aRow[0],
                (string)$aRow[1],
                $aRow[2]
            );
        }

        return $this;
    }

    /**
     * @param Condition $Condition
     *
     * @return $this
     */
    public function sub($Condition)
    {
        $this->aData[] = $Condition;

        return $this;
    }
}
