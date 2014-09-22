<?php
class app {
    static public function init(){
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));
        url::run();
        $confFile    = M_DIR.'conf/config.php';
        $hookFile    = M_DIR.'conf/hooks.php';
        $langFile    = M_DIR.'lang/'.C('DEFAULT_LANG').'.php';
        $funcFile    = M_DIR.'common/function.php';
        if(is_file($confFile))      C(include $confFile);            // 加载当前分组配置
        if(is_file($hookFile))      C('self', include $hookFile);    // 加载当前分组钩子
        if(is_file($langFile))      L(include $langFile);            // 加载当前分组语言包
        if(is_file($funcFile))      require_cache($funcFile);        // 加载当前分组函数
        define('NOW_TIME',          $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD',    $_SERVER['REQUEST_METHOD']);
        define('IS_GET',            REQUEST_METHOD =='GET' ? true : false);
        define('IS_POST',           REQUEST_METHOD =='POST' ? true : false);
        define('IS_PUT',            REQUEST_METHOD =='PUT' ? true : false);
        define('IS_DELETE',         REQUEST_METHOD =='DELETE' ? true : false);
        define('IS_AJAX',           ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')])) ? true : false);
        hook('url_dispatch');
        if(C('VAR_FILTERS')) {
            $filters = explode(',', C('VAR_FILTERS'));
            foreach($filters as $filter) {
                array_walk_recursive($_POST, $filter);
                array_walk_recursive($_GET, $filter);
            }
        }
        return;
    }
    static public function exec() {
        if(!preg_match('/^[A-Za-z](\w)*$/', C_NAME)) {
            $module = false ;
        }else{
            $module = A(C_NAME);
        }

        if(!$module) {
            $module = A('empty');
            if(!$module){ _404("无法加载模块：".C_NAME);}
        }

        $action = A_NAME;
        try{
            if(!preg_match('/^[A-Za-z](\w)*$/', $action)) {
                kernel::halt("非法操作名!");
            }

            $method = new ReflectionMethod($module, $action);
            if($method->isPublic()) {
                $class = new ReflectionClass($module);
                // 前置操作
                if($class->hasMethod('_before_'.$action)) {
                    $before = $class->getMethod('_before_'.$action);
                    if($before->isPublic()) $before->invoke($module);
                }

                // 执行操作
                $method->invoke($module);

                // 后置操作
                if($class->hasMethod('_after_'.$action)) {
                    $after = $class->getMethod('_after_'.$action);
                    if($after->isPublic()) $after->invoke($module);
                }
            }else{
                kernel::halt("请求方法禁止访问");
            }
        }catch(ReflectionException $e){
            $method = new ReflectionMethod($module, '__call');
            $method->invokeArgs($module, array($action,''));
            exit();
        }
        return ;

    }
    static public function run() {
        hook('app_init');
        self::init();

        hook('app_begin');
        session(C('SESSION_OPTION'));

        G('initTime');
        self::exec();

        hook('app_end');
        return;
    }
}