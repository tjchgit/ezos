<?php
function is_administrator($uid = null){
    echo  '1111111111111111';
}

function is_login(){
    echo '1111111112222222222222';
}

function demo($o){
    return $o/5;
}

function userMakeFunc(String $data){
    list($val1, $val2) = array_values($data);
    return $val1 != $val2;
}