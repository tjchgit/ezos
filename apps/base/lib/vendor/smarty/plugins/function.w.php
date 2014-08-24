<?php
function smarty_function_w($param){
    $name   = isset($param['name']) ? $param['name'] : '' ;
    $data   = isset($param['data']) ? $param['data'] : array() ;
    $return = isset($param['return']) ? $param['return']!=false : true ;
    $content =  W($name, $data, $return);
    return $content;
}