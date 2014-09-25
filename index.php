<?php
/**
 *  项目入口
 *  网站所有对请求都会走这个文件
 **/
# 开启调试模式 上线后删除本行
define('APP_DEBUG', true);

# 引入框架启动文件
include './apps/base/kernel.php';

# 启动框架
kernel::boot();
