<?php
/**
 *  页面输出压缩控制
 */
class base_hok_compress extends hook{
    public function run(&$content){
         $content = $this->compressReplace($content);
    }
    protected function compressReplace($string){
        $string=str_replace("\r\n",'',$string); //清除换行符 
        $string=str_replace("\n",'',$string);   //清除换行符
        $string=str_replace("\t",'',$string);   //清除制表符
        $pattern=array(
            "/> *([^ ]*) *</",                  //去掉注释标记
            "/[\s]+/",
            "/<!--[^!]*-->/",
            "/\" /",
            "/ \"/",
            "'/\*[^*]*\*/'"
        );
        $replace=array (
            ">\\1<",
            " ",
            "",
            "\"",
            "\"",
            ""
        );
        return preg_replace($pattern, $replace, $string);
    }
}