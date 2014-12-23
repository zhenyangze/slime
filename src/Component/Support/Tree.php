<?php
namespace Slime\Component\Support;

/**
 * Class Tree
 *
 * @package Slime\Component\Support
 * @author  smallslime@gmaile.com
 *
 * @example
<code>
 * $Root = new Node('root');
 * $N1 = new Node('music');
 * $N2 = new Node('video');
 * $N3 = new Node('book');
 * $N1_1 = new Node('R&B');
 *
 * $N1_1->appendTo($N1);
 * $N1->appendTo($Root);
 * $N2->appendTo($Root);
 * $N3->appendTo($Root);
 *
 * foreach ($Root as $iLevel => $mV) {
 * var_dump($iLevel, $mV);
 * }
 * </code>
 */
class Tree implements \IteratorAggregate
{
    /**
     * @var Tree[]
     */
    public $aChildren = array();

    /** @var null | Tree */
    protected $Parent;

    protected $mValue;

    /**
     * @param mixed       $mValue
     * @param null | Tree $Parent
     */
    public function __construct($mValue, $Parent = null)
    {
        $this->mValue = $mValue;
        $this->Parent = $Parent;
    }

    private function _addChild(Tree $ChildNode)
    {
        $this->aChildren[] = $ChildNode;
    }

    /**
     * @param Tree $ParentNode
     */
    public function appendTo(Tree $ParentNode)
    {
        $this->Parent = $ParentNode;
        $ParentNode->_addChild($this);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->mValue;
    }

    /**
     * @return Tree[]
     */
    public function getChildren()
    {
        return $this->aChildren;
    }

    /**
     * @param int $i
     *
     * @return null|Tree
     */
    public function getChild($i)
    {
        return isset($this->aChildren[$i]) ? $this->aChildren[$i] : null;
    }

    /**
     * @return Tree[]
     */
    public function getBrother()
    {
        $aArr = $this->getParent()->getChildren();
        foreach ($aArr as $iK => $Item) {
            if ($Item === $this) {
                unset($aArr[$iK]);
            }
        }
        return $aArr;
    }

    /**
     * @return null|Tree
     */
    public function getParent()
    {
        return $this->Parent;
    }

    /**
     * @param int $iMax
     *
     * @return null|Tree
     */
    public function getForbear($iMax = 0)
    {
        if ($iMax <= 0) {
            $iMax = -1;
            $i    = -2;
        } else {
            $i = 0;
        }
        $P = $this;
        while ($i < $iMax) {
            $PP = $P->getParent();
            if ($i >= 0) {
                $i++;
            }
            if ($P === null) {
                break;
            } else {
                $P = $PP;
            }
        }

        return $P;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        $i = -1;
        do {
            $P = $this->getParent();
            $i++;
        } while ($P === null);
        return $i;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or
     *       <b>Traversable</b>
     */
    public function getIterator()
    {
        return new DeepIterator($this);
    }
}

class DeepIterator implements \Iterator
{
    protected $aMap = array();
    protected $iCurHeight = 0;

    public function __construct(Tree $Tree)
    {
        $PreNode = new Tree(true);
        $Tree->appendTo($PreNode);

        $this->BaseNode    = $Tree;
        $this->CurrentNode = $Tree;
        $this->PreNode     = $PreNode;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->CurrentNode->getValue();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        /** @var Tree $P */
        $P = $this->CurrentNode;
        do {
            if ($P === $this->PreNode) {
                $this->CurrentNode = $P;
                break;
            }

            $sPHash = spl_object_hash($P);
            $iIndex = $this->aMap[$sPHash] + 1;
            if (($CurNode = $P->getChild($iIndex)) !== null) {
                $this->iCurHeight++;
                $this->CurrentNode   = $CurNode;
                $this->aMap[$sPHash] = $iIndex;
                break;
            }

            $this->iCurHeight--;
            $P = $P->getParent();
        } while (true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->iCurHeight;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        $this->aMap[spl_object_hash($this->CurrentNode)] = -1;
        return $this->CurrentNode !== $this->PreNode;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->CurrentNode = $this->BaseNode;
        $this->aMap        = array(spl_object_hash($this->PreNode) => -1);
    }
}