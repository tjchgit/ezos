<?php
class base_hok_replace extends hook {
    protected $options   =  array(
        'TMPL_PARSE_STRING' =>  array(),
    );
    // 行为扩展的执行入口必须是run
    public function run(&$content){
        $content = $this->templateContentReplace($content);
    }
    /**
     * 模板内容替换
     * @access protected
     * @param string $content 模板内容
     * @return string
     */
    protected function templateContentReplace($content) {
        // 系统默认的特殊变量替换
        $replace =  array(
            '__TMPL__'      => THEME_PATH,              // 项目模板目录
            '../public'     => THEME_PATH.'public',     // 项目公共模板目录
            '__PUBLIC__'    => ROOT_PATH.'public',      // 站点公共目录
            '__STATIC__'    => G_PATH.'static',          // 分组静态文件
        );
        // 允许用户自定义模板的字符串替换
        if(is_array(C('TMPL_PARSE_STRING')) )
            $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));
        $content = str_replace(array_keys($replace),    array_values($replace), $content);
        return $content;
    }
}