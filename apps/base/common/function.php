<?php
/**
 * A函数用于实例化模块 格式：分组/模块
 * @param string $name 模块资源地址
 * @return Action|false
 */
function A($name) {
    static $_action = array();
    if(strpos($name, '/')) {
        list($group, $name) = explode('/', $name);
    }else{
        $group  = G_NAME ;
        $name   = $name ;
    }
    $class = $group.'_ctl_'.$name;
    if(isset($_action[$class])) return $_action[$class];    // 如果当前类已经存在 直接返回

    if(class_exists($class)) {
        $action = new $class();
        $_action[$class] = $action;
        return $action;
    }else{
        return false;
    }
}

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @return mixed
 */
function C($name=null, $value=null) {
    static $_config = array();
    if (empty($name))  return $_config;
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = $value;
            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0]   =  strtolower($name[0]);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    if (is_array($name)){
        $_config = array_merge($_config, array_change_key_case($name));
        return;
    }
    return null;
}

/**
 * D函数用于实例化Model 格式 项目://分组/模块
 * @param string $name Model资源地址
 * @param string $layer 业务层名称
 * @return Model
 */
function D($name, $layer=''){
    if(empty($name)) return new Model;
    static $_model  =   array();
    $layer          =   $layer ? $layer : C('DEFAULT_MODEL_LAYER');
    if(strpos($name,'/')){
        list($group,$model) = explode('/', $name);
    }else{
        $group = G_NAME;
        $model = $name;
    }
    $className = $group."_mdl_".$model;
    if(isset($_model[$className]))   return $_model[$className];
    if(class_exists($className)){
        $class = new $className();
    }
    $_model[$className] = $class;
    return $class;
}

/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @return void
 */
function E($msg, $code=0) {
    throw new except($msg, $code);
}

/**
 * 缓存管理
 * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value 缓存值
 * @param mixed $options 缓存参数
 * @return mixed
 */
function S($name, $value='', $options=null) {
    static $cache = '';
    if( is_array($options) && empty($cache) ) {
        // 缓存操作 同时初始化
        $type = isset($options['type']) ? $options['type'] : '';
        $cache = cache::getInstance($type, $options);
    }elseif(is_array($name)) {
        $type   = isset($name['type']) ? $name['type'] : '';
        $cache  = cache::getInstance($type, $name);
    }elseif(empty($cache)) {
        $cache  = cache::getInstance();
    }

    if('' === $value) {
        return $cache->get($name);
    }elseif(is_null($value)) {
        return $cache->rm($value);
    }else{
        if(is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : null;
        }else{
            $expire = is_numeric($options) ? $options : null;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 * @param string $name 缓存名称
 * @param mixed $value 缓存值
 * @param string $path 缓存路径
 * @return mixed
 */
function F($name, $value='', $path=DATA_DIR) {
    static $_cache  = array();
    $filename       = $path . $name . '.php';
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            return false !== strpos($name,'*')?array_map("unlink", glob($filename)):unlink($filename);
        } else {
            // 缓存数据
            $dir            =   dirname($filename);
            // 目录不存在则创建
            if (!is_dir($dir))
                mkdir($dir,0755,true);
            $_cache[$name]  =   $value;
            return file_put_contents($filename, strip_whitespace("<?php\treturn " . var_export($value, true) . ";?>"));
        }
    }
    if (isset($_cache[$name])){
        return $_cache[$name];
    }
    // 获取缓存数据
    if (is_file($filename)) {
        $value          =   include $filename;
        $_cache[$name]  =   $value;
    } else {
        $value          =   false;
    }
    return $value;
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * </code>
 * @param string $start 开始标签
 * @param string $end 结束标签
 * @param integer|string $dec 小数位
 * @return mixed
 */
function G($start, $end='', $dec=4) {
    static $_info       =   array();
    if(is_float($end)) { // 记录时间
        $_info[$start]  =   $end;
    }elseif(!empty($end)){ // 统计时间和内存使用
        if(!isset($_info[$end])) $_info[$end]       =  microtime(TRUE);
        return number_format(($_info[$end]-$_info[$start]),$dec);
    }else{ // 记录时间和内存使用
        $_info[$start]  =  microtime(TRUE);
    }
}

/**
 * 执行某个钩子
 * @param string $name 钩子名称
 * @param Mixed $params 传入的参数
 * @return void
 */
function H($name, &$params=null) {
    if(strpos($name,'/')){
        list($name,$method) = explode('/',$name);
    }else{
        $method     =   'run';
    }
    $class      = $name;
    if(APP_DEBUG){
        G('hookStart');
    }
    $hook   =  new $class();
    $hook   -> $method($params);
    if(APP_DEBUG) {
        G('hookEnd');
        kernel::trace("{$name}::{$method} 耗时:".G('hookStart','hookEnd',6)."s", '', 'INFO');
    }
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @return mixed
 */
function I($name,$default='',$filter=null) {
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input  =  $_GET;
            }
            if(C('VAR_URL_PARAMS') && isset($_GET[C('VAR_URL_PARAMS')])){
                $input  =   array_merge($input,$_GET[C('VAR_URL_PARAMS')]);
            }
            break;
        case 'request' :   $input =& $_REQUEST;   break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        default:
            return null;
    }
    if(C('VAR_FILTERS')) {      // 全局过滤
        $_filters    =   explode(',',C('VAR_FILTERS'));
        foreach($_filters as $_filter){
            // 全局参数过滤
            array_walk_recursive($input,$_filter);
        }
    }
    if(empty($name)) { // 获取全部变量
        $data       =   $input;
        $filters    =   isset($filter) ? $filter : C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                //$data   =   array_map($filter, $data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =	$input[$name];
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return	 isset($default)?$default:null;
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =	 isset($default)?$default:null;
    }
    return $data;
}

/**
 * M函数用于实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model  = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class      =   'model';
    }
    $guid           =   $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid])){
        $_model[$guid] = new $class($name,$tablePrefix,$connection);
    }
    return $_model[$guid];
}

/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 * @param string $key 标识位置
 * @param integer $step 步进值
 * @return mixed
 */
function N($key, $step=0, $save=false) {
    static $_num    = array();
    if (!isset($_num[$key])) {
        $_num[$key] = (false !== $save)? S('N_'.$key) :  0;
    }
    if (empty($step)){
        return $_num[$key];
    }else{
        $_num[$key] = $_num[$key] + (int) $step;
    }
    if(false !== $save){ // 保存结果
        S('N_'.$key,$_num[$key],$save);
    }
}

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为true 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function P($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else{
        return $output;
    }
}

/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[分组/模块/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param string|array $vars 传入的参数，支持数组和字符串
 * @param string $suffix 伪静态后缀，默认为true表示获取配置值
 * @param boolean $domain 是否显示域名
 * @return string
 */
function U($url='',$vars='',$suffix=true) {
    // 解析URL
    $info   =  parse_url($url);
    $url    =  !empty($info['path']) ? $info['path'] : ACTION_NAME ;
    if(isset($info['fragment'])) {              // 解析锚点
        $anchor =   $info['fragment'];
        if(false !== strpos($anchor,'?')) {     // 解析参数
            list($anchor,$info['query']) = explode('?',$anchor,2);
        }
        if(false !== strpos($anchor,'@')) {     // 解析域名
            list($anchor,$host)    =   explode('@',$anchor, 2);
        }
    }elseif(false !== strpos($url,'@')) {       // 解析域名
        list($url,$host)    =   explode('@',$info['path'], 2);
    }
    // 解析参数
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // 解析地址里面参数 合并到vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }

    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    if($url) {
        if(0=== strpos($url,'/')) {// 定义路由
            $route      =   true;
            $url        =   substr($url,1);
            if('/' != $depr) {
                $url    =   str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // 安全替换
                $url    =   str_replace('/',$depr,$url);
            }
            // 解析分组、模块和操作
            $url        =   trim($url,$depr);
            $path       =   explode($depr,$url);
            $var        =   array();
            $var[C('VAR_ACTION')]       =   !empty($path) ? array_pop($path) : A_NAME;
            $var[C('VAR_MODULE')]       =   !empty($path) ? array_pop($path) : M_NAME;
            if(!empty($path)) {
                $group                  =   array_pop($path);
                $var[C('VAR_GROUP')]    =   $group;
            }else{
                if(G_NAME != C('DEFAULT_GROUP')) {
                    $var[C('VAR_GROUP')]=   G_NAME;
                }
            }
        }
    }

    if(C('URL_MODEL') == 0) { // 普通模式URL转换
        $url        =   http_build_query(array_reverse($var));
        if(!empty($vars)) {
            $vars   =   urldecode(http_build_query($vars));
            $url   .=   '&'.$vars;
        }
    }else{ // PATHINFO模式或者兼容URL模式
        if(isset($route)) {
            $url    =   rtrim($url,$depr);
        }else{
            $url    =   implode($depr,array_reverse($var));
        }
        if(!empty($vars)) { // 添加参数
            foreach ($vars as $var => $val){
                if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
            }
        }
    }
    if(isset($anchor)){
        $url  .= '#'.$anchor;
    }
    $domain = preg_replace('/\w+?\.php(\/|\?)?/i', '', __WEB__);
    $suffix = $suffix===true ? '.'.ltrim(C('URL_HTML_SUFFIX'), '.') : '' ;
    return $domain.url::tourl($url).$suffix;
}

/**
 * 渲染输出Widget
 * @param string $name Widget名称
 * @param array $data 传入的参数
 * @param boolean $return 是否返回内容 
 * @param string $path Widget所在路径
 * @return void
 */
function W($name, $data=array(), $return=false){
    if(strpos($name, '/')){
        list($group, $name) = explode('/', $name);
    }else{
        $group  = G_NAME;
        $name   = $name;
    }
    $class = $group.'_wid_'.$name;
    if(class_exists($class)){
        $widget = new $class();
        $content = $widget->run($data);
        if($return){
            return $content;
        }else{
            echo $content;
        }
    }else{
        kernel::halt("没有找到类定义文件：".$class);
    }
}

/**
 * session管理函数
 * @param string|array $name session名称 如果为数组则表示进行session设置
 * @param mixed $value session值
 * @return mixed
 */
function session($name,$value='') {
    $prefix   =  C('SESSION_PREFIX');
    if(is_array($name)) {
        if(isset($name['prefix'])) C('SESSION_PREFIX',$name['prefix']);
        if(C('VAR_SESSION_ID') && isset($_REQUEST[C('VAR_SESSION_ID')])){
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        }elseif(isset($name['id'])) {
            session_id($name['id']);
        }
        ini_set('session.auto_start', 0);
        if(isset($name['name'])             && $name['name'] != '')            session_name($name['name']);
        if(isset($name['path'])             && $name['path'] != '')            session_save_path($name['path']);
        if(isset($name['domain'])           && $name['domain'] != '')          ini_set('session.cookie_domain', $name['domain']);
        if(isset($name['expire'])           && $name['expire'] != '')          ini_set('session.gc_maxlifetime', $name['expire']);
        if(isset($name['use_trans_sid'])    && $name['use_trans_sid'] != '')   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
        if(isset($name['use_cookies'])      && $name['use_cookies'] != '')     ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        if(isset($name['cache_limiter'])    && $name['cache_limiter'] != '')   session_cache_limiter($name['cache_limiter']);
        if(isset($name['cache_expire'])     && $name['cache_expire'] != '')    session_cache_expire($name['cache_expire']);
        if(isset($name['type'])             && $name['type'] != '')            C('SESSION_TYPE',$name['type']);
        if(C('SESSION_TYPE')) {
            $class      = 'Session'. ucwords(strtolower(C('SESSION_TYPE')));
            if(require_cache(EXTEND_PATH.'Driver/Session/'.$class.'.class.php')) {
                $hander = new $class();
                $hander->execute();
            }else {
                // 类没有定义
                kernel::throwException('没有找到类定义文件: ' . $class);
            }
        }
        // 启动session
        if(C('SESSION_AUTO_START'))  session_start();
    }elseif('' === $value) {
        if(0===strpos($name,'[')) {         // session 操作
            if('[pause]'==$name){           // 暂停session
                session_write_close();
            }elseif('[start]'==$name){      // 启动session
                session_start();
            }elseif('[destroy]'==$name){    // 销毁session
                $_SESSION =  array();
                session_unset();
                session_destroy();
            }elseif('[regenerate]'==$name){ // 重新生成id
                session_regenerate_id();
            }
        }elseif(0===strpos($name,'?')){     // 检查session
            $name   =  substr($name,1);
            if(strpos($name,'.')){          // 支持数组
                list($name1,$name2) =   explode('.',$name);
                return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
            }else{
                return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
            }
        }elseif(is_null($name)){            // 清空session
            if($prefix) {
                unset($_SESSION[$prefix]);
            }else{
                $_SESSION = array();
            }
        }elseif($prefix){                   // 获取session
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$prefix][$name1][$name2])?$_SESSION[$prefix][$name1][$name2]:null;
            }else{
                return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;
            }
        }else{
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;
            }else{
                return isset($_SESSION[$name])?$_SESSION[$name]:null;
            }
        }
    }elseif(is_null($value)) {
        if($prefix){
            unset($_SESSION[$prefix][$name]);
        }else{
            unset($_SESSION[$name]);
        }
    }else{
        if($prefix){
            if (!is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name]   =  $value;
        }else{
            $_SESSION[$name]  =  $value;
        }
    }
}

/**
 * Cookie 设置、获取、删除
 * @param string $name cookie名称
 * @param mixed $value cookie值
 * @param mixed $options cookie参数
 * @return mixed
 */
function cookie($name, $value='', $option=null) {
    // 默认设置
    $config = array(
        'prefix'    =>  C('COOKIE_PREFIX'),     // cookie 名称前缀
        'expire'    =>  C('COOKIE_EXPIRE'),     // cookie 保存时间
        'path'      =>  C('COOKIE_PATH'),       // cookie 保存路径
        'domain'    =>  C('COOKIE_DOMAIN'),     // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (!is_null($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config     = array_merge($config, array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE)) return;
        // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }
    $name = $config['prefix'] . $name;
    if ('' === $value) {
        if(isset($_COOKIE[$name])){
            $value =    $_COOKIE[$name];
            if(0===strpos($value,'cent:')){
                $value  =   substr($value,6);
                return array_map('urldecode',json_decode(MAGIC_QUOTES_GPC ? stripslashes($value) : $value, true));
            }else{
                return $value;
            }
        }else{
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if(is_array($value)){
                $value  = 'cent:'.json_encode(array_map('urlencode',$value));
            }
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}

/**
 *   递归获取指定路径下的所有文件或匹配指定正则的文件（不包括“.”和“..”），结果以数组形式返回
 *   @param  string  $dir
 *   @param  string  $pattern
 *   @return array
 */
function file_list($dir,$pattern=""){
    $arr=array();
    $dir_handle=opendir($dir);
    if($dir_handle){
        // 这里必须严格比较，因为返回的文件名可能是“0”
        while(($file=readdir($dir_handle))!==false){
            if($file==='.' || $file==='..') continue;
            $tmp=realpath($dir.'/'.$file);
            if(is_dir($tmp)){
                $retArr=file_list($tmp,$pattern);
                if(!empty($retArr)) $arr[]=$retArr;
            }else{
                if($pattern==="" || preg_match($pattern,$tmp)) $arr[]=$tmp;
            }
        }
        closedir($dir_handle);
    }
    return $arr;
}

/**钩子函数**/
function hook($hook, &$params=null) {
    $_extends   = C('extends.'.$hook);
    $_hooks     = C('hooks.'.$hook);
    $_self      = C('self.'.$hook);
    if(!empty($_hooks)) {
        $_hooks = array_unique(array_merge($_extends, $_hooks));
    }else{
        $_hooks = $_extends;
    }
    if(!empty($_self)){
        $_hooks = array_unique(array_merge($_hooks, $_self));
    }
    if($_hooks) {
        if(APP_DEBUG) {
            G($hook.'Start');
            kernel::trace("[ {$hook} ]",'','INFO');
        }

        foreach($_hooks as $key=>$name) {
            if(!is_int($key)) $name = $key;
            H($name, $params);
        }


        if(APP_DEBUG) {
            G($hook.'End');
            kernel::trace("{$hook} HOOK整体耗时:".G($hook.'Start', $hook.'End', 6)."s", '', 'INFO');
        }
    }
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
	$type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

// 过滤表单中的表达式
function filter_exp(&$value){
    if (in_array(strtolower($value),array('exp','or'))){
        $value .= ' ';
    }
}

/**
 * 快速导入第三方框架类库 所有第三方框架的类库文件统一放到 系统的extends/vendor目录下面
 * @param string $class 类库
 * @param string $baseUrl 基础目录
 * @param string $ext 类库后缀
 * @return boolean
 */
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl)) $baseUrl = VENDOR_DIR;
    return import($class, $baseUrl, $ext);
}

/**
 * 引入包文件
 * @param class
 * @param baseUrl
 * @param ext
 * @return bool
 */
function import($class, $baseUrl='', $ext ='.php') {
    static $_file = array();
    $class = str_replace(array('.','#'), array('/','.'), $class);
    $class_strut = explode('/', $class);
    if(empty($baseUrl)) {
        if('@' == $class_strut[0]) {
            $baseUrl = APP_DIR.G_NAME.'/';
            unset($class_strut[0]);
            $class = implode('/', $class_strut);
        }elseif(in_array($class_strut[0], array('net', 'crypt', 'util'))) {
            $baseUrl = ORG_DIR;
        }
    }
    $baseUrl    = rtrim($baseUrl, '/').'/';
    $classFile  = $baseUrl . $class . $ext ;
    if( isset($_file[$classFile]) ) return;
    $_file[$classFile] = true ;
    return require_cache($classFile);
}

/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

 /**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) {
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}

/**
 * 404处理
 * 调试模式会抛异常
 * 部署模式下面传入url参数可以指定跳转页面，否则发送404信息
 * @param string $msg 提示信息
 * @param string $url 跳转URL地址
 * @return void
 */
function _404($msg='',$url='') {
    APP_DEBUG && E($msg);
    if($msg && C('LOG_EXCEPTION_RECORD')) log::write($msg);
    if(empty($url) && C('URL_404_REDIRECT')) {
        $url    =   C('URL_404_REDIRECT');
    }
    if($url) {
        redirect($url);
    }else{
        send_http_status(404);
        exit;
    }
}

/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
    $url        = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg    = "系统将在 {$time} 秒之后自动跳转到 {$url} !";
    if (!headers_sent()) {
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content) {
    $stripStr   = '';
    $tokens     = token_get_all($content);  //分析php源码
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr  .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {   //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;

                case T_WHITESPACE:      //过滤空格
                    if (!$last_space) {
                        $stripStr  .= ' ';
                        $last_space = true;
                    }
                    break;

                case T_START_HEREDOC:
                    $stripStr .= "<<<st\n";
                    break;

                case T_END_HEREDOC:
                    $stripStr .= "st;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr  .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * 去除HTML中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function compress_html($string) {
    $string = str_replace("\r\n", '', $string);     //清除换行符
    $string = str_replace("\n", '', $string);       //清除换行符
    $string = str_replace("\t", '', $string);       //清除制表符
    $pattern = array (
        "/> *([^ ]*) *</", //去掉注释标记
        "/[\s]+/",
        "/<!--[^!]*-->/",
        "/\" /",
        "/ \"/",
        "'/\*[^*]*\*/'"
    );
    $replace = array (
        ">\\1<",
        " ",
        "",
        "\"",
        "\"",
        ""
    );
    return preg_replace($pattern, $replace, $string);
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix) {
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    }
    return false;
}

/**
 * 不区分大小写的in_array实现
 * @param value 要查找的值
 * @param array 要查找的数组
 * @return bool  是否存在
 */
function in_array_case($value,$array){
    $value = strtolower($value);
    $array = array_map('strtolower', $array);
    return in_array($value,$array);
}

/**
 * 二维数据val值排序
 * @param arr 要排序的数组
 * @param keys 要排序的KEY
 * @param type 排序方式 默认为asc
 * @return array 排序后的数组
 */
function array_sort($arr,$keys,$type='asc'){
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = $v[$keys];
    }
    $type = strtolower($type);
    if($type == 'asc'){
        asort($keysvalue);
    }else{
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}