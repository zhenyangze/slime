# USAGE
    <?php
    require 'MultiJob.php';

    class Master
    {
        public function __construct()
        {
            $this->aData = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22);
        }

        public function init($iNumOfWorker)
        {
            // 初始化分片
            $this->aData = array_chunk($this->aData, (int)ceil(count($this->aData) / $iNumOfWorker));
        }

        public function getPiece($i, $iPID)
        {
            return $this->aData[$i];
        }
    }

    $Obj = new Master();
    (new \Slime\Component\MultiJob\MultiJob(
        array($Obj, 'init'),
        array($Obj, 'getPiece'),
        function($aData) {
            printf(
                "%s : %s : %s\n",
                getmypid(),
                json_encode($aData),
                array_reduce(
                    $aData,
                    function($iData, $iItem)
                    {
                        $iData+=$iItem;
                        return $iData;
                    }
                )
        );
    },
    5,
    20
    ))->run();
