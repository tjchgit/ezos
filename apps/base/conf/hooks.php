<?php
return array(
    'app_init'          => array(),
    'app_begin'         => array(),
    'route_check'       => array(),
    'app_end'           => array(),
    'path_info'         => array(),
    'controller_begin'  => array(),
    'controller_end'    => array(),
    'view_begin'        => array(),
    'view_parse'        => array('base_hok_parse'),
    'view_filter'       => array('base_hok_replace','base_hok_token'),
    'view_end'          => array('base_hok_trace'),
);