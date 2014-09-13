<?php
function smarty_modifier_cut($string, $len=100, $dot='...'){
    if(!isset($string) || $string=='') return '';
    P($string);
    preg_match_all("/.{1}/", $string, $chars);
    $all    = array();
    $timer  = 0;
    $ch     = '';
    foreach($chars[0] as $char){
        $timer ++ ;
        if(ord($char) > 127){
            $ch .= $char ;
            if($timer == 3){
                $all[]  = $ch ;
                $ch     = '';
                $timer  = 0;
            }
        }else{
            $timer  = 0;
            $all[]  = $char;
        }
    }
    if(sizeof($all)<=$len) return implode('', $all);
    return implode('', array_slice($all, 0, $len)).$dot;
}