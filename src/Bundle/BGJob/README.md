# 实例

1. 创建 daemon.php

        <?php
        require '/your_path_of_psr4_autoload/autoload.php';
        
        $Q = new \Slime\Bundle\BGJob\Queue_SysMsg(dirname(__FILE__));
        $Log = new \Slime\Component\Log\Logger(array('STDFD' => array('@STDFD')), Slime\Component\Log\Logger::LEVEL_INFO);
        \Slime\Bundle\BGJob\Daemon::run($Q, $Log);
        ?>

2. 创建 push.php

        <?php
        require '/your_path_of_psr4_autoload/autoload.php';

        $Q = new \Slime\Bundle\BGJob\Queue_SysMsg(dirname(__FILE__));
        for ($i=0;$i<30;$i++) {
            $Q->push('~/test.php');
        }
        ?>

3. 创建 ~/test.php

        <?php
        var_dump(file_put_contents('/tmp/log', rand(1,1000), FILE_APPEND | LOCK_EX));
        sleep(rand(10,20));
        ?>

4. 运行 php daemon.php 

5. 运行 php push.php