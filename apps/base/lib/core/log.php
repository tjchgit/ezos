<?php
class log {
    // 日志级别 从上到下，由低到高
    const EMERG     = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT     = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT      = 'CRIT';   // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR       = 'ERR';    // 一般错误: 一般性错误
    const WARN      = 'WARN';   // 警告性错误: 需要发出警告的错误
    const NOTICE    = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO      = 'INFO';   // 信息: 程序输出信息
    const DEBUG     = 'DEBUG';  // 调试: 调试信息
    const SQL       = 'SQL';    // SQL：SQL语句 注意只在调试模式开启时有效

    // 日志信息
    static $log = array();
    // 日志存储
    static protected $storage   =   null;
    // 初始化
    static public function init($config=array()){
        $type   = isset($config['type']) ? strtolower($config['type']) : 'file';
        $class  = 'base_driver_log_'.$type;
        unset($config['type']);
        self::$storage = new $class($config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @return void
     */
    static public function record($message, $level=self::ERR, $record=false) {
        if($record || false !== strpos(C('LOG_LEVEL'), $level)) {
            self::$log[] = "{$level}:{$message}\r\n";
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param string $logFile  写入目标
     * @param string $extra 额外参数
     * @return void
     */
     static function save($logFile='', $extra='') {

        if(empty(self::$log)) return;
        // 自动文件名
        $logFile = self::autoLogFileName($logFile);

        // 初始化文件写入驱动
        if(!self::$storage){
            self::init();
        }
        // 获取错误消息
        $message    =   implode('',self::$log);
        // 写日志
        self::$storage->write($message,$logFile);
        // 清空日志缓存
        self::$log = array();
     }

     /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param string $logFile  写入目标
     * @param string $extra 额外参数
     * @return void
     */
     static function write($message,$level=self::ERR, $logFile='',$extra='') {
        $now = date(self::$format);
        // 初始化驱动
        if(!self::$storage){
            self::init();
        }
        // 自动文件名
        $logFile = self::autoLogFileName($logFile);
        self::$storage->write("{$level}: {$message}", $logFile);
    }

    static function autoLogFileName($name){
        if(empty($name)){
            $name = LOG_DIR.M_NAME.'/'.date("Y-m-d").'.log';
        }
        return $name;
    }
}