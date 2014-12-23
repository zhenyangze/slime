# 实例

1. 初始化

        <?php
        require '/your_path_of_psr4_autoload/autoload.php';
        
        $Log = new \Slime\Component\Log\Logger(
            array(
                'File' => array('@File', '/tmp/proj_{level}_{date}.log'),
                'FirePHP' => array('@FirePHP')
            ), 
            Slime\Component\Log\Logger::LEVEL_INFO
        );
        \Slime\Bundle\BGJob\Daemon::run($Q, $Log);
        ?>

2. 使用
