<?php
function smarty_function_u($param){
    $url    = isset($param['url']) ? $param['url'] : '' ;
    $vars   = isset($param['vars']) ? $param['vars'] : '' ;
    $suffix = isset($param['suffix']) ? $param['suffix']!=false : true ;
    return U($url, $vars, $suffix);
}