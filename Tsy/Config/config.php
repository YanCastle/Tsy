<?php
/**
 * Created by PhpStorm.
 * User: Castle
 * Date: 2016/4/11
 * Time: 22:19
 */
return [
//    CLI模式下fd分组的配置，仅用于SwooleServer的情况下
    'CLI_FD_GROUP'=>'cli_fd_group',

    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_TEMP_TYPE'  =>  'File',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       =>  TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_KEY'        =>  '',	// 缓存文件KEY (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  true,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别
//    'LOGIN_REQUIRE'=>[], //已废除
    'SESSION_EXPIRE'=>5600,//session 过期时间

    'SAAS_MODULE'=>[], //针对SaaS模式软件配置哪些模块属于SaaS模块，SaaS模块会加载session('DB_PREFIX')为数据库前缀

    /* 模板引擎设置 */
    'CACHE_PATH'=>TEMP_PATH.DIRECTORY_SEPARATOR.'View'.DIRECTORY_SEPARATOR,
    'TMPL_CONTENT_TYPE'     =>  'text/html', // 默认模板输出类型
//    'TMPL_ACTION_ERROR'     =>  APP.'Tpl/dispatch_jump.tpl', // 默认错误跳转对应的模板文件
//    'TMPL_ACTION_SUCCESS'   =>  THINK_PATH.'Tpl/dispatch_jump.tpl', // 默认成功跳转对应的模板文件
//    'TMPL_EXCEPTION_FILE'   =>  THINK_PATH.'Tpl/think_exception.tpl',// 异常页面的模板文件
    'TMPL_DETECT_THEME'     =>  false,       // 自动侦测模板主题
    'TMPL_TEMPLATE_SUFFIX'  =>  '.html',     // 默认模板文件后缀
    'TMPL_FILE_DEPR'        =>  '/', //模板文件CONTROLLER_NAME与ACTION_NAME之间的分割符
    // 布局设置
    'TMPL_ENGINE_TYPE'      =>  'Think',     // 默认模板引擎 以下设置仅对使用Think模板引擎有效
    'TMPL_CACHFILE_SUFFIX'  =>  '.php',      // 默认模板缓存后缀
    'TMPL_DENY_FUNC_LIST'   =>  'echo,exit',    // 模板引擎禁用函数
    'TMPL_DENY_PHP'         =>  false, // 默认模板引擎是否禁用PHP原生代码
    'TMPL_L_DELIM'          =>  '{',            // 模板引擎普通标签开始标记
    'TMPL_R_DELIM'          =>  '}',            // 模板引擎普通标签结束标记
    'TMPL_VAR_IDENTIFY'     =>  'array',     // 模板变量识别。留空自动判断,参数为'obj'则表示对象
    'TMPL_STRIP_SPACE'      =>  true,       // 是否去除模板文件里面的html空格与换行
    'TMPL_CACHE_ON'         =>  true,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_PREFIX'     =>  '',         // 模板缓存前缀标识，可以动态改变
    'TMPL_CACHE_TIME'       =>  0,         // 模板缓存有效期 0 为永久，(以数字为值，单位:秒)
    'TMPL_LAYOUT_ITEM'      =>  '{__CONTENT__}', // 布局模板的内容替换标识
    'LAYOUT_ON'             =>  false, // 是否启用布局
    'LAYOUT_NAME'           =>  'layout', // 当前布局名称 默认为layout
    'DEFAULT_V_LAYER'=>'View',
    // Think模板引擎标签库相关设定
    'TAGLIB_BEGIN'          =>  '<',  // 标签库标签开始标记
    'TAGLIB_END'            =>  '>',  // 标签库标签结束标记
    'TAGLIB_LOAD'           =>  true, // 是否使用内置标签库之外的其它标签库，默认自动检测
    'TAGLIB_BUILD_IN'       =>  'cx', // 内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
    'TAGLIB_PRE_LOAD'       =>  '',   // 需要额外加载的标签库(须指定标签库名称)，多个以逗号分隔


    'DEFAULT_OUT'=>'json_encode',
    'MSG'=>[
        'YUNTONGXUN'=>[
            'SERVER_IP'=>'sandboxapp.cloopen.com',
            'SERVER_PORT'=>'8883',
            'SOFT_VERSION'=>'2013-12-26',
            'ACCOUNT_SID'=>'',
            'ACCOUNT_TOKEN'=>'',
            'APP_ID'=>''
        ],
    ],
    'MSG_TEMPLATE_DIR'=>APP_PATH.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'MSG',//消息模板的存储地址

    //UCloud相关配置
    'UCLOUD_PUBLIC_KEY'=>'',
    'UCLOUD_PRIVATE_KEY'=>'',

    'FILE_UPLOAD_TYPE'      =>  'Local',    // 文件上传方式
    
    //配置各种模式下获取i参数和数据的格式
    'HTTP'=>[
        'I'=>'get.i',
        'D'=>'post',
        'DISPATCH'=>["\\Tsy\\Tsy::\$Mode",'dispatch'],
        'OUT'=>["\\Tsy\\Tsy::\$Mode",'output'],
    ],
    'SQL_PREFIX'=>['{$PREFIX}','prefix_']
];