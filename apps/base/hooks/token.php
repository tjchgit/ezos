<?php
class base_hok_token extends hook {

    public function run(&$content){
        if(C('TOKEN_ON')) {
            if(strpos($content,'#__TOKEN__#')) {                            // 指定表单令牌隐藏域位置
                $content = str_replace('#__TOKEN__#',$this->buildToken(),$content);
            }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {      // 智能生成表单令牌隐藏域
                $content = str_replace($match[0],$this->buildToken().$match[0],$content);
            }
        }else{
            $content = str_replace('{__TOKEN__}','',$content);
        }
    }

    private function buildToken() {
        $tokenName  = C('TOKEN_NAME');
        $tokenType  = C('TOKEN_TYPE');
        if(!isset($_SESSION[$tokenName])) {
            $_SESSION[$tokenName]  = array();
        }
        // 标识当前页面唯一性
        $tokenKey   =  md5($_SERVER['REQUEST_URI']);
        if(isset($_SESSION[$tokenName][$tokenKey])) {       // 相同页面不重复生成session
            $tokenValue = $_SESSION[$tokenName][$tokenKey];
        }else{
            $tokenValue = $tokenType(microtime(TRUE));
            $_SESSION[$tokenName][$tokenKey]   =  $tokenValue;
        }
        $token      =  '<input type="hidden" name="'.$tokenName.'" value="'.$tokenKey.'_'.$tokenValue.'" />';
        return $token;
    }

}