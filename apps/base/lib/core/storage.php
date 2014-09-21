<?php
class storage{
    static protected $handler;
    /**
     * 连接分布式文件系统
     * @access public
     * @param string $type 文件类型
     * @param array $options  配置数组
     * @return void
     **/
    static public function connect($type='file', $option=array()){
        $class = 'base_driver_storage_'.$type;
        self::$handler = new $class($option);
    }

    /**
     * 调用缓存驱动的方法
     **/
    static public function __callstatic($method,$args){
        //调用缓存驱动的方法
        if(method_exists(self::$handler, $method)){
           return call_user_func_array(array(self::$handler,$method), $args);
        }
    }
}