<?php
class url {
    static public function run(){

        // 子域名部署实现
        if(C('SUB_DOMAIN_DEPLOY')){
            $rules      = C('SUB_DOMAIN_RULES');
            if(isset($rules[$_SERVER['HTTP_HOST']])) {
                $rule = $rules[$_SERVER['HTTP_HOST']];
            }else{
                $subDomain  = strtolower(substr($_SERVER['HTTP_HOST'],0,strpos($_SERVER['HTTP_HOST'],'.')));
                if($subDomain && isset($rules[$subDomain])) {
                    $rule =  $rules[$subDomain];
                }else if(isset($rules['*'])){
                    if('www' != $subDomain) $rule =  $rules['*'];
                }else{
                    $rule = array();
                }
            }
            if(!empty($rule)) {
                $array  = explode('/',$rule);
                $group = array_shift($array);
                if(!empty($group)) {
                    $_GET[C('VAR_GROUP')]  =   $group;
                }
                if(!empty($array)) {
                    $_GET[C('VAR_MODULE')]   =   array_shift($array);
                }
            }
        }
        $args = self::formatUrl();
        $var = array();
        if(!isset($_GET[C('VAR_GROUP')])) {
            $var[C('VAR_GROUP')] = (isset($args[0]) && is_dir(APP_DIR.$args[0])) ? array_shift($args) : C('DEFAULT_GROUP');
        }
        if(!isset($_GET[C('VAR_MODULE')])) {
            $var[C('VAR_MODULE')] = array_shift($args);
        }
        $var[C('VAR_ACTION')] = array_shift($args);
        if (!empty($args)) {
            $count = count($args);
            for ($i = 0; $i < $count;) {
                $_GET[$args [$i]] = isset($args [$i + 1]) ? $args [$i + 1] : '';
                $i += 2;
            }
        }
        $_GET = array_merge($_GET, $var);
        if(C('URL_MODEL') == URL_COMPAT){
            unset($_GET[C('VAR_PATHINFO')]);
        }
        self::setConst();
    }

    static public function setConst(){
        $host = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
        defined('__HOST__') or define("__HOST__", is_ssl() ? "https://" : "http://" .$host);
        $script_file = rtrim($_SERVER['SCRIPT_NAME'],'/');
        $root = rtrim(dirname($script_file),'/');
        defined('__ROOT__') or define("__ROOT__", __HOST__ . ($root=='/' || $root=='\\'?'':$root));
        defined('__WEB__') or define("__WEB__", __HOST__ . $_SERVER['SCRIPT_NAME']);
        defined('__URL__') or define("__URL__", __HOST__ . '/' . trim($_SERVER['REQUEST_URI'],'/'));
        define("__HISTORY__", isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:null);
        define('G_NAME',    self::getName(C('VAR_GROUP'),   C('DEFAULT_GROUP')));
        define('M_NAME',    self::getName(C('VAR_MODULE'),  C('DEFAULT_MODEL')));
        define('A_NAME',    self::getName(C('VAR_ACTION'),  C('DEFAULT_ACTION')));
        define('G_DIR',     APP_DIR.G_NAME.'/');
        define('G_PATH',    APP_PATH.G_NAME.'/');
        define('M_DIR',     G_DIR.'model/');
        define('V_DIR',     G_DIR.'view/');
        define('C_DIR',     G_DIR.'controller/');
        return ;
    }

    static public function formatUrl(){
        $urlModel = C('URL_MODEL');
        if($urlModel== URL_COMPAT && isset($_GET[C('VAR_PATHINFO')])){
            $query = $_GET[C("PATHINFO_VAR")];  // 兼容模式
        }elseif ($urlModel == URL_REWRITE && isset($_SERVER['PATH_INFO'])){
            $query = $_SERVER['PATH_INFO'];     // PATHINFO
        }else{
            $query = $_SERVER['QUERY_STRING'];  // 其它模式
        }
        $url = self::parseRoute(str_ireplace(C('URL_HTML_SUFFIX'), '', trim($query, '/')));
        $gets = '';
        if($urlModel == URL_REWRITE || $urlModel == URL_COMPAT && isset($_GET[C('VAR_PATHINFO')])){
            $url = str_replace(array('&', '='), C("URL_PATHINFO_DEPR"), $url);
        }else{
            parse_str($url, $gets);
            $_GET = array_merge($_GET, $gets);
        }

        return $gets || empty($url) ? array() : explode(C("URL_PATHINFO_DEPR"), $url);
    }

    static public function parseRoute($query){
        $route = C("ROUTE");
        if (!$route || !is_array($route)) return $query;
        foreach ($route as $k => $v) {
            if (substr($k, 0, 1) === '/') {
                if (preg_match("@^/.*/[isUx]*$@i", $k)) {       // 正则路由
                    if (preg_match($k, $query)) {               // 如果匹配URL地址
                        $v = str_replace('#', '\\', $v);        // 元子组替换
                        return preg_replace($k, $v, $query);    // 匹配当前正则路由,url按正则替换
                    }
                    continue;
                }
            }else{
                if($k == $query) return $v ;
            }
        }
        return $query;
    }

    static public function getName($var, $default) {
        if($_GET[$var] == 'index.php'){
            $_GET[$var] = $default;
        }
        $name = (!empty($_GET[$var])) ? $_GET[$var] : $default;
        unset($_GET[$var]);
        return strip_tags(strtolower($name));
    }

    static public function tourl($url){
        $route = C("ROUTE");
        if(!$route) return $url ;
        foreach ($route as $k => $v) {
            $k = trim($k);
            if (substr($k, 0, 1) !== '/') {     // 静态路由 
                $url = preg_replace('@^'.$v.'$@i', $k, $url);
                return trim($url, '/');
            }else{                              // 正则路由
                $regGroup = array();
                preg_match_all("@\(.*?\)@i", $k, $regGroup, PREG_PATTERN_ORDER);
                $searchRegExp = $v;
                for ($i = 0, $total = count($regGroup[0]); $i < $total; $i++) {
                    $searchRegExp = str_replace('#' . ($i + 1), $regGroup[0][$i], $searchRegExp);
                }
                $urlArgs = array();
                preg_match_all("@^" . $searchRegExp . "$@i", $url, $urlArgs, PREG_SET_ORDER);
                if ($urlArgs) {
                    $url = trim(preg_replace(array('@/\^@', '@/[isUx]$@','@\$@'), array('','',''), $k), '/');
                    foreach ($regGroup[0] as $key => $val) {
                        $val = preg_replace('@([\*\$\(\)\+\?\[\]\{\}\\\])@', '\\\$1', $val);
                        $url = preg_replace('@' . $val . '@', $urlArgs[0][$key + 1], $url, $count = 1);
                    }
                    return trim($url, '/');
                }
            }
        }
        return trim($url, '/');
    }
}