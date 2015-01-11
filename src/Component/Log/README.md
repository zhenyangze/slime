# 实例

1. 初始化

        <?php
        $Log = new \Slime\Component\Log\Logger(
            // 数组为多个Writer, 每次 Log 调用会依次 call 各个 Writer 完成其独立逻辑
            // key为标记, 可以通过$Log->aWriter['File']获取单个Writer
            // value数组中, 第0个元素为Writer类名, ~表示Slime\Component\Log\Writer_, 后面元素依次作为构造函数参数传入
            array(
                'File'    => array('~File', '/tmp/proj_{level}_{date}.log'),
                'FirePHP' => array('~FirePHP')
            ),  
            
            // 需要 Log 的级别
            Slime\Component\Log\Logger::LEVEL_INFO,
            
            // 此 Log 对象的唯一ID
            // 对于每个请求, 都有一个新的 Log 对象, 有了这个唯一ID, Writer可以在写入时使用此ID, 查看日志时方便找出某一次请求的所有Log
            // 默认为 null , 自动生成
            null, 
            
            // 写入 Log 的内容的最大长度(注意如果你设置为200, 实际储存[197长度 + ...]), 
            // 参数为int(>3)/null(不限制) 
            null
        );
        ?>

2. 使用

        <?php
        $Log->info("hello world");
        $Log->warn("test {for}", array('foo' => 'bar');
        ?>
        
3. 内置 Writer 功能及实例化方式, 详见各个 Writer 构造函数说明
