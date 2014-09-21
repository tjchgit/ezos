<?php
class base_hok_parse extends hook {
    public function run(&$_data) {
        $_content           = empty($_data['content']) ? $_data['file'] : $_data['contet'];
        $_data['prefix']    = empty($_data['prefix']) ? C('TMPL_CACHE_PREFIX') : $_data['prefix'] ;
        $tpl = new template();
        $tpl->fetch($_content,$_data['var'],$_data['prefix']);
    }

    /**
     * 检查缓存文件是否有效
     * 如果无效则需要重新编译
     * @access protected
     * @param string $tmplTemplateFile  模板文件名
     * @return boolean
     **/
    protected function checkCache($tmplTemplateFile ){

    }

    /**
     * 检查缓存内容是否有效
     * 如果无效则需要重新编译
     * @access protected
     * @param string $tmplContent  模板内容
     * @return boolean
     **/
    protected function checkContentCache($tmplContent){

    }
}