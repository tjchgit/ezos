<?php
function smarty_function_i($param){
    $name       = isset($param['name']) ? $param['name'] : '' ;
    $default    = isset($param['default']) ? $param['default'] : '' ;
    $filter     = isset($param['filter']) ? $param['filter'] : null ;
    return I($name, $default, $filter);
}