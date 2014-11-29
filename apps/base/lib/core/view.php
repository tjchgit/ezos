<?php
class view {
    protected $tVar     = array();
    protected $theme    = '';

    /**
     * 像模版分配变量
     */
    public function assign($name, $value='') {
        if(is_array($name)) {
            $this->tVar = array_merge($this->tVar, $name);
        }else{
            $this->tVar[$name]  = $value ;
        }
    }

    /**
     * 取得模板变量的值
     */
    public function get($name='') {
        if('' == $name) {
            return $this->tVar;
        }
        return isset($this->tVar[$name]) ? $this->tVar[$name] : false ;
    }

     /**
     * 加载模板和页面输出 可以返回输出内容
     * @access public
     * @param string $templateFile  模板文件名
     * @param string $charset       模板输出字符集
     * @param string $contentType   输出类型
     * @param string $content       模板输出内容
     * @param string $prefix        模板缓存前缀
     * @return mixed
     */
    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        G('viewStartTime');
        // 视图开始标签
        hook('view_begin',$templateFile);

        // 解析并获取模板内容
        $content = $this->fetch($templateFile,$content,$prefix);

        // 输出模板内容
        $this->render($content,$charset,$contentType);

        // 视图结束标签
        hook('view_end');
    }

     /**
     * 输出内容文本可以包括Html
     * @access private
     * @param string $content 输出内容
     * @param string $charset 模板输出字符集
     * @param string $contentType 输出类型
     * @return mixed
     */
    private function render($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // 网页字符编码
        header('Content-Type:'.$contentType.'; charset='.$charset);
        // 输出模板文件
        echo $content;
    }

    /**
     * 解析和获取模板内容 用于输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param string $content 模板输出内容
     * @param string $prefix 模板缓存前缀
     * @return string
     */
    public function fetch($templateFile='',$content='',$prefix='') {
        if(empty($content)) {
            $templateFile   =   $this->parseTemplate($templateFile);
            if(!is_file($templateFile)) E("模版:[".$templateFile."]不存在");      // 模板文件不存在直接返回
        }
        ob_start();                     // 页面缓存
        ob_implicit_flush(0);
        $params = array('var'=>$this->tVar, 'file'=>$templateFile, 'content'=>$content, 'prefix'=>$prefix);
        hook('view_parse', $params);

        $content = ob_get_clean();      // 获取并清空缓存

        hook('view_filter',$content);   // 内容过滤标签
        return $content;                // 输出模板文件
    }
    /**
     * 自动定位模板文件
     * @access protected
     * @param string $template 模板文件规则
     * @return string
     */
    public function parseTemplate($template='') {
        if(is_file($template)){ return $template; }

        if(strpos($template, '@')) {
            list($group, $template) = explode('@', $template);
        }else{
            $group = M_NAME;
        }
        $depr       =   C('TMPL_FILE_DEPR');
        $template   =   str_replace(':', $depr, $template);
        $theme  =   $this->getTemplateTheme();                                      // 当前主题名称
        define('THEME_DIR', APP_DIR.$group.'/'.C('DEFAULT_VIEW_LAYER').'/'.$theme);     // 当前主题的模版路径
        define('THEME_PATH',   APP_PATH.$group.'/'.C('DEFAULT_VIEW_LAYER').'/'.$theme); // 获取相对路径

        if('' == $template) {                                                       // 分析模板文件规则
            $template = C_NAME . $depr . A_NAME;
        }elseif(false === strpos($template, '/')){
            $template = C_NAME . $depr . $template;
        }
        return THEME_DIR.$template.C('TMPL_TEMPLATE_SUFFIX');
    }

    /**
     * 设置当前模版
     */
     public function theme($theme) {
        $this->theme = $theme;
        return $this;
     }

    /**
     * 获取当前的模板主题
     */
     private function getTemplateTheme() {
        if($this->theme) {                  // 指定模板主题
            $theme = $this->theme;
        }else{
            return _getThemes();
        }
    }
}