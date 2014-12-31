通常用于CLI下的多进程组件

USAGE

```

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
        array($Obj, 'init'),                   // master 初始化回调(可以为null)
        array($Obj, 'getPiece'),               // worker 获取自己需要处理的任务回调(可以为null)
        function($aData) {                     // worker业务逻辑回调, $aData 为第二个参数回调的结果
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
    5,                                         // worker 数
    20                                         // 同时存在最大进程数. null表示不限制, 直接按worker数量并发处理
    ))->run();
```