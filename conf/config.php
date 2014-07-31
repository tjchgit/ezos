<?php
return array(
    'URL_MODEL'         => 2,
    'DATA_CACHE_TYPE'   => 'file',
    'ROUTE'             => array(
        '/^m_(\d+)$/'           => 'wechat/index/run/id/#1',
        '/^m_(\d+)_(\d+)$/'     => 'wechat/index/run/id/#1/uid/#2',
        '/^m_(\d+)_p(\d+)$/'    => 'wechat/index/run/id/#1/pid/#2',
        '/^h_(\d+)_(\d+)$/'     => 'home/index/run/id/#1/uid/#2',
        'login'                 => 'public/login',
        'register'              => 'public/register',
        'getpassword'           => 'public/getpassword',
        'upload'                => 'public/upload',
    ),
    'DB_TYPE'               =>  'mysql',            // 数据库类型
    'DB_HOST'               =>  'localhost',        // 服务器地址
    'DB_NAME'               =>  'weixin',           // 数据库名
    'DB_USER'               =>  'all',              // 用户名
    'DB_PWD'                =>  'ckFP8tIL',         // 密码
    'DB_PREFIX'             =>  'wx_',              // 数据库表前缀

    'SUB_DOMAIN_DEPLOY'     => 1,
    'SUB_DOMAIN_RULES'      => array(
        'ezos'              => 'home',
        'admin'             => 'admin',
        'server'            => 'server',
    ),
);