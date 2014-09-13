<?php
class base_driver_tpl_smarty {
    public function fetch($templateFile, $var) {
        $templateFile = substr($templateFile, strlen(THEME_DIR));
        vendor('smarty.Smarty#class');
        $tpl = new Smarty();
        $tpl->caching       = C('TMPL_CACHE_ON');
        $tpl->template_dir  = THEME_DIR;
        $tpl->compile_dir   = COMP_DIR.M_NAME.'/';
        $tpl->cache_dir     = CACHE_DIR.M_NAME.'/';
        if(C('TMPL_ENGINE_CONFIG')) {
            $config = C('TMPL_ENGINE_CONFIG');
            foreach($config as $key=>$val) {
                $tpl->{$key}    = $val ;
            }
        }
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
}