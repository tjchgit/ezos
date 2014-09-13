<?php
/**
 * 缓存管理类
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class cache {
    protected $handler    ;
    protected $options = array();
    public function connect($type='', $options=array()) {
        if(empty($type)) {
            $type = C('DATA_CACHE_TYPE');
        }
        $type = strtolower($type);
        $class = "base_driver_cache_".$type;
        if(class_exists($class)) {
            $cache = new $class($options);
        }else{
            E("没有找到缓存类：".$type);
        }
        return $cache;
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    public function __unset($name) {
        return $this->rm($name);
    }

    public function setOptions($name){
        return $this->options[$name];
    }

    static function getInstance() {
        $param = func_get_args();
        return kernel::instance(__CLASS__, 'connect', $param);
    }

    protected function queue($key) {
        static $_handler = array(
            'file'      => array('F','F'),
            'xcache'    => array('xcache_get', 'xcache_set'),
            'apc'       => array('apc_fetch', 'apc_store'),
        );
        $queue      = isset($this->options['queue']) ? $this->options['queue'] : 'file';
        $fun        = isset($_handler[$queue]) ? $_handler[$queue] : $_handler['file'];
        $queue_name = isset($this->options['queue_name']) ? $this->options['queue_name'] : 'cent_queue';
        $value      = $fun[0]($queue_name);
        if(!$value) {
            $value = array();
        }

        if(false === array_search($key, $value)) {
            array_push($value,$key);
        }

        if(count($value) > $this->options['length']) {
            $key = array_shift($value);

            $this->rm($key);
            if(APP_DEBUG) {
                N($queue_name.'_out_times',1,true);
            }
        }
        return $fun[1]($queue_name, $value);
    }

    public function __call($method, $args) {
        if(method_exists($this->handler, $method)) {
            return call_user_func_array(array($this->handler,$method),$args);
        }else{
            E("在类:".__CLASS__."未找到".$method."方法");
            return;
        }
    }

}