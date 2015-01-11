CONFIG
=====

通过 \Slime\Component\Config::Configure 创建一个实现了 \Slime\Component\Config\IAdaptor 接口的 Config 对象
    
初始化
=====
    
    $Cache = \Slime\Component\Config::Configure('~PHP', '/tmp/cache' , 0777, null)
        
说明
=====
初始化方式与 Cache 类似

使用
=====
参照接口 IAdaptor
    