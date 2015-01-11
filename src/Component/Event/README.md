Event
=====

事件对象
    
初始化
=====
    
    $Event = new \Slime\Component\Event\Event();
        
使用
=====
    //监听事件
    $Event->listen(
         //监听位置, 也可以是数组, 表示监听多个位置, 例如 array('system_run', 'app_run')
         'system_run',
         
         // 回调函数
         function() {}
         
         // 优先级, 大的先调用, 默认为 0
         0,
         
         // 此回调事件名称, 若填写可以 forget 注销, 若为 null 无法被单独注销, 默认为 null
         'cb_for_system_run',
         
         // 当时的环境变量, 若传递此参数, 在回调时会加载回调参数最后, 进行调用. 默认 array()
         array($PDO, $MC, $REDIS)
    );
    
    //事件发生
    $Event->fire(
        'system_run',
        
        // 注册的回调函数的参数 默认 array()
        array('argv1', 222, $ABC)
    );
    
    //注销事件
    $Event->forget(
        // 注销此位置的回调事件
        'system_run',
        
        // 若设置此值, 注销 system_run 位置 特定事件 cb_for_system_run . 若为 null 则注销此位置全部事件. 默认为 null
    );
