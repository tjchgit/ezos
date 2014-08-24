<?php
class base_hok_parse extends hook {
    public function run(&$_data) {
        $engine             = strtolower( C('TMPL_ENGINE_TYPE') );
        $_content           = empty($_data['content']) ? $_data['file'] : $_data['contet'];
        $_data['prefix']    = empty($_data['prefix']) ? C('TMPL_CACHE_PREFIX') : $_data['prefix'] ;
        $class              = 'base_driver_tpl_'.$engine;
        if(class_exists($class)) {
            $tpl    = new $class;
            $tpl -> fetch($_content, $_data['var']);
        }else{
            E("系统不支持：".$class);
        }
    }
}