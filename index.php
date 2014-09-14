<?php

# 开启调试模式 上线后删除本行
define('APP_DEBUG', true);

# 引入框架启动文件
include './apps/base/kernel.php';

# 启动框架
kernel::boot();
