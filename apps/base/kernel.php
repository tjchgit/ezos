<?php

// 记录开始时间
$GLOBALS['_begintime'] = microtime(true);

// 记录内存使用状态
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();

// URL模式常量定义
const URL_COMMON        =   0;  //普通模式
const URL_PATHINFO      =   1;  //PATHINFO模式
const URL_REWRITE       =   2;  //REWRITE模式
const URL_COMPAT        =   3;  // 兼容模式

// 运行模式定义
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli' ? 1   :   0);
if(!IS_CLI) {
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',    rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('ROOT_PATH')) {
        $_root  =   rtrim(dirname(_PHP_FILE_),'/');
        define('ROOT_PATH',  ( ($_root=='/' || $_root=='\\') ? '' : $_root ).'/');
    }
}

// 目录定义
defined('ROOT_DIR')     or define('ROOT_DIR', str_replace('\\', '/', substr(dirname(__FILE__), 0, -9)));
defined('APP_DEBUG')    or define('APP_DEBUG',  false);
defined('APP_DIR')      or define('APP_DIR',    ROOT_DIR.'apps/');
defined('APP_PATH')     or define('APP_PATH',   ROOT_PATH.'apps/');
defined('CONF_DIR')     or define('CONF_DIR',   ROOT_DIR.'conf/');
defined('TEMP_DIR')     or define('TEMP_DIR',   ROOT_DIR.'temp/');
defined('BASE_DIR')     or define('BASE_DIR',   APP_DIR.'base/');
defined('BASE_PATH')    or define('BASE_PATH',  APP_PATH.'base/');
defined('LIB_DIR')      or define('LIB_DIR',    BASE_DIR.'lib/');
defined('LIB_PATH')     or define('LIB_PATH',   BASE_PATH.'lib/');
defined('CORE_DIR')     or define('CORE_DIR',   LIB_DIR.'core/');
defined('CORE_PATH')    or define('CORE_PATH',  LIB_PATH.'core/');
defined('COMMON_DIR')   or define('COMMON_DIR', BASE_DIR.'common/');
defined('COMMON_PATH')  or define('COMMON_PATH',BASE_PATH.'common/');
defined('LOG_DIR')      or define('LOG_DIR',    TEMP_DIR.'logs/');
defined('DATA_DIR')     or define('DATA_DIR',   TEMP_DIR.'data/');
defined('CACHE_DIR')    or define('CACHE_DIR',  TEMP_DIR.'cache/');

// 扩展文件夹
defined('VENDOR_DIR')   or define('VENDOR_DIR', LIB_DIR.'vendor/');

class kernel {
    static public function boot() {
        # 注册类自动加载方法
        spl_autoload_register('kernel::autoload');
        # 错误调试相关信息
        register_shutdown_function('kernel::fatalError');
        set_error_handler('kernel::appError');
        set_exception_handler('kernel::appException');
        # 开始启动加载必须的文件
        self::buildApp();
        # 记录开始时间
        G("loadTime");
        # 文件储存方式
        storage::connect("file");
        # 加载火狐调试类库
        vendor('firephp.fb');
        # 开始运行
        app::run();
    }

    static public function buildApp() {
        include COMMON_DIR.'functions.php';
        C(include BASE_DIR.'conf/config.php');
        C('extends', include BASE_DIR.'conf/hooks.php');
        $hook = CONF_DIR.'hooks.php';
        if(file_exists($hook)) C('hooks', include $hook);
        $conf   = CONF_DIR.'config.php';
        if(file_exists($conf)) C( include $conf );
    }

    /**
     * 类库自动加载
     * @param string $class 对象类名
     * @return void
     */
    static public function autoload($class_name) {
        $className = $class_name;
        $p = strpos($className, '_');
        if($p) {
            $owner      = substr($className, 0, $p);
            if($owner == 'Smarty') return true;
            $className  = substr($className, $p+1);
            $tick       = substr($className, 0, 4);
            $path       = str_replace('_','/',substr($className,4)).'.php';
            switch($tick) {
                case 'ctl_':
                    $fileName = $owner."/".C('DEFAULT_CONTROLLER_LAYER')."/".$path;
                break;
                case 'mdl_':
                    $fileName = $owner."/".C('DEFAULT_MODEL_LAYER')."/".$path;
                break;
                case 'hok_':
                    $fileName = $owner."/".C('DEFAULT_HOOKS_LAYER')."/".$path;
                break;
                case 'wid_':
                    $fileName = $owner."/".C('DEFAULT_WIDGET_LAYER')."/".$path;
                break;
                default :
                    $fileName = $owner."/lib/".str_replace('_','/',$className).'.php';
            }
            $CUSTOM_NOW         = C('CUSTOM_NOW');
            $CUSTOM_CORE_DIR    = C('CUSTOM_CORE_DIR');
            if($CUSTOM_NOW && file_exists($CUSTOM_CORE_DIR.$fileName)) {
                require_cache($CUSTOM_CORE_DIR.$fileName);
            }elseif(file_exists(APP_DIR.$fileName)) {
                require_cache(APP_DIR.$fileName);
            }else{
                self::halt("未找到类:{$class_name}定义文件");
                exit;
            }
        }elseif(file_exists(CORE_DIR.$className.'.php')) {
            require_cache(CORE_DIR.$className.'.php');
        }else{
            self::halt("未找到类:{$class_name}定义文件");
            return false;
        }
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @return object
     */
    static public function instance($name, $method='', $args=array()) {
        static $_instance = array();
        $identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);
        if (!isset($_instance[$identify])) {
            if (class_exists($name)) {
                $o = new $name();
                if (method_exists($o, $method)) {
                    if (!empty($args)) {
                        $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                    } else {
                        $_instance[$identify] = $o->$method();
                    }
                }else{
                    $_instance[$identify] = $o;
                }
            }else{
                self::halt('没有定义类:' . $name);
            }
        }
        return $_instance[$identify];
    }

    /**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    static public function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $trace              =   $e->getTrace();
        if('E'==$trace[0]['function']) {
            $error['file']  =   $trace[0]['file'];
            $error['line']  =   $trace[0]['line'];
        }else{
            $error['file']  =   $e->getFile();
            $error['line']  =   $e->getLine();
        }
        $error['trace']     =   $e->getTraceAsString();
        log::record($error['message'], log::ERR);
        // 发送404信息
        header('HTTP/1.1 404 Not Found');
        header('Status:404 Not Found');
        self::halt($error);
    }

    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                $errorStr = "$errstr ".$errfile." 第 $errline 行.";
                if(C('LOG_RECORD')) Log::write("[$errno] ".$errorStr,Log::ERR);
                self::halt($errorStr);
            break;
            case E_STRICT:
            case E_USER_WARNING:
            case E_USER_NOTICE:
            default:
                $errorStr = "[$errno] $errstr ".$errfile." 第 $errline 行.";
                self::trace($errorStr,'','NOTIC');
            break;
        }
    }
    // 致命错误捕获
    static public function fatalError() {
        log::save();
        if ($e = error_get_last()) {
            switch($e['type']){
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:
                ob_end_clean();
                self::halt($e);
                break;
            }
        }
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
     */
    static public function halt($error) {
        $e = array();
        if(APP_DEBUG || IS_CLI) {
            if (!is_array($error)) {
                $trace          = debug_backtrace();
                $e['message']   = $error;
                $e['file']      = $trace[0]['file'];
                $e['line']      = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace']     = ob_get_clean();
            } else {
                $e              = $error;
            }
            if(IS_CLI){
                exit($e['message'].PHP_EOL.'FILE: '.$e['file'].'('.$e['line'].')'.PHP_EOL.$e['trace']);
            }
            if(!C('TEMP_EXCEPTION_FILE')){
                exit('<b>Error:</b>'.$e['message'].' in <b> '.$e['file'].' </b> on line <b>'.$e['line'].'</b>');
            }
            include C('TEMP_EXCEPTION_FILE');
        }else{
            if (C('SHOW_ERROR_MSG')){
                $e['message'] = is_array($error) ? $error['message'] : $error;
            }else{
                $e['message'] = C('ERROR_MESSAGE');
            }
            send_http_status(503);
        }
        die();
    }

    /**
     * 添加和获取页面Trace记录
     * @param string $value 变量
     * @param string $label 标签
     * @param string $level 日志级别(或者页面Trace的选项卡)
     * @param boolean $record 是否记录日志
     * @return void
     */
     static public function trace($value='[cent]',$label='',$level='DEBUG',$record=false) {
        static $_trace =  array();
        if('[cent]' === $value){ // 获取trace信息
            return $_trace;
        }else{
            $info   =   ( $label ? $label.':' : '' ).print_r($value,true);
            if('ERR' == $level && C('TRACE_EXCEPTION')) {           // 抛出异常
                E($info);
            }
            $level  =   strtoupper($level);
            if( !isset($_trace[$level]) ) {
                $_trace[$level] =   array();
            }
            $_trace[$level][]   =   $info;

            if( (defined('IS_AJAX') && IS_AJAX) || ! C('SHOW_PAGE_TRACE')  || $record) {
                log::record($info, $level, $record);
            }
        }
    }
}