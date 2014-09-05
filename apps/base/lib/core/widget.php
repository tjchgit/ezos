<?php
abstract class widget{
    protected $template =  '';
    abstract public function run($data);
    /**
     * 渲染模板输出 供run方法内部调用
     * @access public
     * @param string $templateFile  模板文件
     * @param mixed $var  模板变量
     * @return string
     */
    protected function renderFile($templateFile='',$var='') {
        ob_start();
        ob_implicit_flush(0);
        if(!is_file($templateFile)){
            $name           = explode('_wid_', get_class($this));
            $groupName      = $name[0];
            $templatePath   = APP_DIR.$groupName.'/widget/';
            $templateFile   = $name[1].C('TMPL_TEMPL_SUFFIX');
        }else{
            $groupName      = M_NAME;
            $templatePath   = '';
        }
        $template   =  strtolower( $this->template ? $this->template :( C('TMPL_ENGINE_TYPE') ? C('TMPL_ENGINE_TYPE') : 'php'));
        if($template != 'php'){
            vendor('smarty.Smarty#class');
            $tpl = new Smarty();
            $tpl->caching       = C('TMPL_CACHE_ON');
            $tpl->template_dir  = $templatePath;
            $tpl->compile_dir   = COMP_DIR.$groupName.'/widget/';
            $tpl->cache_dir     = CACHE_DIR.$groupName.'/widget/';
            $tpl->assign('var', $var);
            $tpl->display($templateFile);
        }
        $content = ob_get_clean();
        return $content;
    }
}