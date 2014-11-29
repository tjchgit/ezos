<?php
class api {
    static API_VERSION = '1.0.0';
    public function __construct(){

    }
    public function isAjax(){

    }
    public function getActionName(){

    }
    public function error($code){
        $arr = array(
            'code' => $code,
            'message' => L('__'.$code.'__');
        );
    }
    public function success(){

    }
    public function run(){
        $apiFile = M_DIR.'api/'.self::API_VERSION.'/'.C_NAME.'.php';
        if(is_file($apiFile)){
            require_cache($apiFile);
        }else{
            $this->error(404);
        }
    }
}