<?php
class base_hok_parse extends hook {
    public function run(&$_data) {
        $_content           = empty($_data['content']) ? $_data['file'] : $_data['contet'];
        $_data['prefix']    = empty($_data['prefix']) ? C('TMPL_CACHE_PREFIX') : $_data['prefix'] ;
        if($this->checkCache($_data['file'],$_data['prefix'])){
            // 缓存有效
            $tempFile = CACHE_DIR.M_NAME.'/'.$_data['prefix'].md5($_data['file']).C('TMPL_CACHFILE_SUFFIX');
            storage::load($tempFile, $_data['var']);
        }else{
            // 缓存无效
            $tpl = new template();
            $tpl->fetch($_content, $_data['var'], $_data['prefix']);
        }
    }

    /**
     * 检查缓存文件是否有效
     * 如果无效则需要重新编译
     * @access protected
     * @param string $tmplTemplateFile  模板文件名
     * @return boolean
     **/
    protected function checkCache($tmplTemplateFile, $prefix=''){
        if(!C('TMPL_CACHE_ON')){
            return false;
        }
        $tmplCacheFile = CACHE_DIR.M_NAME.'/'.$prefix.md5($tmplTemplateFile).C('TMPL_CACHFILE_SUFFIX');

        if(!storage::has($tmplCacheFile)){
            return false;
        }else if( filemtime($tmplTemplateFile) > storage::get($tmplCacheFile,'mtime')){
            return false;
        }else if( C('TMPL_CACHE_TIME') != 0 && time() > storage::get($tmplCacheFile,'mtime')+C('TMPL_CACHE_TIME')){
            return false;
        }
        return true;
    }
}