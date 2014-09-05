<?php
return array(
    /*****************************************基本参数********************************************/
    'URL_MODEL'                 => 0,                       // url模式
    'URL_PATHINFO_DEPR'         => '/',                     // PATHINFO 间隔符号
    'URL_HTML_SUFFIX'           => '.html',                 // URL伪静态后缀
    'APP_GROUP_LIST'            => array('base'),           // 应用分组列表
    'SUB_DOMAIN_ON'             => true,                    // 开启子域名部署
    'SUB_DOMAIN_RULES'          => array(),                 // 子域部署规则
    'URL_ROUTE_ON'              => true,                    // 开启路由
    'URL_ROUTE_RULES'           => array(),                 // 路由规则
    /*****************************************默认配置********************************************/
    'DEFAULT_TIMEZONE'          => 'PRC',                   // 默认时区
    'DEFAULT_LANG'              => 'zh-cn',                 // 默认语言
    'DEFAULT_MODULE'            => 'home',                  // 默认分组
    'DEFAULT_CONTROLLER'        => 'index',                 // 默认操作
    'DEFAULT_ACTION'            => 'run',                   // 默认方法
    'DEFAULT_CHARSET'           => 'utf-8',                 // 默认字符集
    'DEFAULT_THEME'             => '',                      // 默认主题
    'DEFAULT_FILTER'            => 'htmlspecialchars',      // 参数默认过滤
    'DEFAULT_VIEW_LAYER'        => 'view',
    'DEFAULT_MODEL_LAYER'       => 'model',
    'DEFAULT_CONTROLLER_LAYER'  => 'controller',
    'DEFAULT_HOOKS_LAYER'       => 'hooks',
    'DEFAULT_WIDGET_LAYER'      => 'widget',
    /*****************************************模版配置********************************************/
    'THEME_LIST'                => array(),                 // 主题目录
    'TMPL_FILE_DEPR'            => '_',                     // 模版间隔
    'TMPL_TEMPL_SUFFIX'         => '.htm',                  // 模版后缀
    'TMPL_CACHE_ON'             => false,                   // 模版缓存
    'TMPL_DETECT_THEME'         => true,                    // 开启自动切换主题
    'TMPL_ENGINE_TYPE'          => 'smarty',                // 默认模版引擎
    'TMPL_CACHE_PREFIX'         => 'ezos_',                 // 模版缓存前缀
    'TMPL_ENGINE_CONFIG'        => array(                   // 模版配置
        'debugging'             => false,                   // 开启调试模式
        'left_delimiter'        =>'{',                      // 左侧边界符
        'right_delimiter'       =>'}',                      // 右侧边界符号
    ),
    'TMPL_CONTENT_TYPE'         => 'text/html',             // 默认输出格式
    'HTTP_CACHE_CONTROL'        => 'private',               // 网页缓存控制
    'TEMP_EXCEPTION_FILE'       => BASE_DIR.'/view/exception.php',// 异常抛出模版
    /*****************************************URL变量********************************************/
    'VAR_TEMPLATE'              => 't',                     // 模版切换变量
    'VAR_PATHINFO'              => 'r',                     // 兼容模式PATHINFO获取变量
    'VAR_MODULE'                => 'm',                     // 分组变量
    'VAR_CONTROLLER'            => 'c',                     // 模型变量
    'VAR_ACTION'                => 'a',                     // 操作变量
    'VAR_AJAX_SUBMIT'           => 'ajax',                  // 默认的AJAX提交变量
    'VAR_FILTERS'               => 'filter_exp',            // 全局过滤方法 用逗号分割
    'VAR_PAGE'                  => 'p',                     // 分页参数
    /*****************************************会话控制********************************************/
    'COOKIE_PREFIX'             => 'cent_',                 // cookie 名称前缀
    'COOKIE_EXPIRE'             => 3600*24*365*10,          // cookie 保存时间
    'COOKIE_PATH'               => '/',                     // cookie 保存路径
    'COOKIE_DOMAIN'             => '',                      // cookie 有效域名
    'SESSION_PREFIX'            => '',                      // 前缀
    'SESSION_AUTO_START'        => true,                    // 自动开启
    'SESSION_OPTION'            => array(                   // 配置信息
        'name'                  => 'cent_session_cookie',   // cookie名称
        'path'                  => '',                      // 路径
        'domain'                => '',                      // 作用域
        'expire'                => '',                      //
        'use_trans_sid'         => '',                      //
        'use_cookies'           => 1,                       //
        'cache_limiter'         => '',                      //
        'cache_expire'          => '',                      //
        'type'                  => '',                      //
    ),
    /*****************************************数据库配置********************************************/
    'DB_TYPE'               =>  '',                         // 数据库类型
    'DB_HOST'               =>  '',                         // 服务器地址
    'DB_NAME'               =>  '',                         // 数据库名
    'DB_USER'               =>  '',                         // 用户名
    'DB_PWD'                =>  '',                         // 密码
    'DB_PORT'               =>  '',                         // 端口
    'DB_PREFIX'             =>  '',                         // 数据库表前缀
    'DB_FIELDTYPE_CHECK'    =>  false,                      // 是否进行字段类型检查
    'DB_CHARSET'            =>  'utf8',                     // 数据库编码默认采用utf8
    'DB_DEPLOY_TYPE'        =>  0,                          // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
    'DB_RW_SEPARATE'        =>  false,                      // 数据库读写是否分离 主从式有效
    'DB_MASTER_NUM'         =>  1,                          // 读写分离后 主服务器数量
    'DB_SLAVE_NO'           =>  '',                         // 指定从服务器序号
    'DB_SQL_BUILD_CACHE'    =>  false,                      // 数据库查询的SQL创建缓存
    'DB_SQL_BUILD_QUEUE'    =>  'file',                     // SQL缓存队列的缓存方式 支持 file xcache和apc
    'DB_SQL_BUILD_LENGTH'   =>  20,                         // SQL缓存的队列长度
    'DB_SQL_LOG'            =>  true,                       // SQL执行日志记录
    'DB_BIND_PARAM'         =>  false,                      // 数据库写入数据自动参数绑定
    'DB_FIELDS_CACHE'       =>  true,                       // 启用字段缓存
    'DB_FIELD_VERSION'      => '1.0.0',                     // 数据缓存版本号
    /*****************************************日志配置********************************************/
    'LOG_RECORD'                => true,                    // 默认记录日志
    'LOG_EXCEPTION_RECORD'      => true,                    // 是否记录异常信息日志
    'LOG_LEVEL'                 => 'EMERG,ALERT,CRIT,ERR',  // 允许记录的日志级别、
    /*****************************************缓存配置********************************************/
    'DATA_CACHE_TYPE'           => 'file',                  // 缓存储存方式
    'DATA_CACHE_PATH'           => CACHE_DIR,               // 缓存目录
    'DATA_CACHE_PREFIX'         => '',                      // 缓存前缀
    'DATA_CACHE_TIME'           => 60*60*24,                // 缓存24小时
    'DATA_CACHE_TABLE'          => 'wx_cache',              // 缓存数据库表名
    'DATA_CACHE_CHECK'          => true,                    // 缓存数据校验
    /*****************************************调试配置*******************************************/
    'URL_404_REDIRECT'          => '',                      // 404跳转页面
    'SHOW_PAGE_TRACE'           => false,                   // 是否开启trace
    'PAGE_TRACE_SAVE'           => true,                    // 是否储存trace
    /*****************************************二开配置********************************************/
    'CUSTOM_IS_OPEN'            => false,                   // 是否二次开发
    'CUSTOM_CORE_DIR'           => '',                      // 二开目录
    /*****************************************文件上传********************************************/
    'FILE_UPLOAD_TYPE'          => 'local',                 // 文件上传
    'UPLOAD_TYPE_CONFIG'        => array(),                 // 文件上传配置
    /*****************************************权限认证********************************************/
    'AUTH_CONFIG'               => array(
        'AUTH_ON'               => true,                    // 是否开启权限认证
        'AUTH_TYPE'             => 2,                       // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'            => 'auth_group',            // 用户组数据表名
        'AUTH_GROUP_ACCESS'     => 'auth_group_access',     // 用户-用户组关系表
        'AUTH_RULE'             => 'auth_rule',             // 权限规则表
        'AUTH_USER'             => 'web_user',              // 户信息表
        'AUTO_PREFIX'           => true,                    // 自动前缀
    ),
    /*****************************************自动验证扩展********************************************/
    'VALIDATE_EXTENDS'          => array(
        'require'               => '/.+/',
    ),
);