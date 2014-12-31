CACHE
=====

通过 \Slime\Component\Cache::Factory 创建一个实现了 \Slime\Component\Cache\IAdaptor 接口的 Cache 对象
    
例如
=====
    
    $Cache = \Slime\Component\Cache::Factory('~File', '/tmp/cache' , 0777, null)
        
说明
=====
1. 例中第一个参数 ~File, ~表示 \Slime\Component\Cache\Adaptor_ , 所以若您自己完成一个 class MyCache implements \Slime\Component\Cache\IAdaptor :

        $Cache = \Slime\Component\Cache::Factory('/YourNS/MyCache', $param1 , $param2);

2. 例中第一个参数后的所有参数, 将作为第一个参数表示的类实例化时的参数, 按照顺序依次传入.