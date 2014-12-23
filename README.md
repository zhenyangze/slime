Slime
=====

Slime 是一个简洁 合理 易于扩展的 WEB 开发框架

前言
=====
1. 框架遵循 [psr4](http://www.php-fig.org/psr/psr-4/) 规范
2. Log 组件遵循 [psr3](http://www.php-fig.org/psr/psr-3/) 规范
3. 变量命名方式, 用 小写类型+大写开头驼峰命名例, 类型
    1. i:整数, s:字符串, f:浮点数, a:数组, r:资源型, n:null, 对象无类型前缀直接大写, m:(mixed)可能表示多重类型
    2. 例:
        1. $iUser     
            据变量名即可知表示用户数, 整数类型.
        2. $niUser    
            同上, 同时可以为null
        4. $nsiVar
            表示变量可以为null/字符串/整数
        5. $UserModel
            表示对象
        6. $m_n_aUsers_sUser
            表示此变量可以是null/数组/字符串
4. 函数命名方式基本遵循 小驼峰(动作+描述), 例: getStatus()
5. 框架核心为 Context + Components + 框架调度 .

    结构图:
    @todo
        
组件文档
=====
[Cache](src/Component/Cache/)

[Config](src/Component/Config/)

[Event](src/Component/Event/)

[Http](src/Component/Http/)

[I18N](src/Component/I18N/)

[Lock](src/Component/Lock/)

[Log](src/Component/Log/)

[NoSQL](src/Component/NoSQL/)

[RDBMS](src/Component/RDBMS/)

[Route](src/Component/Route/)

[Security](src/Component/Security/)

[Support](src/Component/Support/)

[View](src/Component/View/)