<?php
class base_driver_taglib_e extends taglib{
    protected $tag = array(
        'select'    => array('attr'=>'name,id,data,value'),
        'radio'     => array('attr'=>'name,id,data,value'),
        'list'      => array()
    );

    public function _select($tag){

    }

    public function _radio($tag){

    }

    public function _list($tag){

    }
}