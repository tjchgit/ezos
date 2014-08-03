<?php
class base_driver_cache_db extends cache {
    /**
     *  CREATE TABLE `wx_cache` (
     *  `key` char(32) NOT NULL DEFAULT '' COMMENT '缓存KEY',
     *  `data` text NOT NULL COMMENT '缓存数据',
     *  `crc` char(32) NOT NULL DEFAULT '' COMMENT '缓存唯一校验',
     *  `expire` int(11) NOT NULL DEFAULT '0' COMMENT '过期时间',
     *  PRIMARY KEY (`key`)
     *  ) ENGINE=MyISAM DEFAULT CHARSET=utf8
     **/

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if(!empty($options)) {
            $this->options  =   $options;
        }
        $this->options['table']  = isset($options['table']) ? $options['table'] : C('DATA_CACHE_TABLE');
        $this->options['prefix'] = isset($options['prefix']) ? $options['prefix'] : C('DATA_CACHE_PREFIX');
        $this->options['length'] = isset($options['length']) ? $options['length'] : 0;        
        $this->options['expire'] = isset($options['expire']) ? $options['expire'] : C('DATA_CACHE_TIME');
        $this->handler   = db::getInstance();
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $name       =  MD5($this->options['prefix'].addslashes($name));
        N('cache_read',1);
        $result     =  $this->handler->query('SELECT `data`,`crc` FROM `'.$this->options['table'].'` WHERE `key`=\''.$name.'\' AND (`expire` =0 OR `expire`>'.time().') LIMIT 0,1');
        if(false !== $result ) {
            $result   =  $result[0];
            if(C('DATA_CACHE_CHECK')) {//开启数据校验
                if($result['crc'] != md5($result['data'])) {//校验错误
                    return false;
                }
            }
            $content   =  $result['data'];
            if(C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
                //启用数据压缩
                $content   =   gzuncompress($content);
            }
            $content    =   unserialize($content);
            return $content;
        }
        else {
            return false;
        }
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value,$expire=null) {
        $data   =  serialize($value);
        $name   =  MD5($this->options['prefix'].addslashes($name));
        N('cache_write',1);
        $crc = C('DATA_CACHE_CHECK') ? md5($data) : '';
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $expire     =   ($expire==0) ? 0 : (time()+$expire) ;//缓存有效期为0表示永久缓存
        $result     =   $this->handler->query('select `key` from `'.$this->options['table'].'` where `key`=\''.$name.'\' limit 0,1');
        if(!empty($result) ) {  //更新记录
            $result  =  $this->handler->execute('UPDATE '.$this->options['table'].' SET data=\''.$data.'\' ,crc=\''.$crc.'\',expire='.$expire.' WHERE `key`=\''.$name.'\'');
        }else {                 //新增记录
            $result  =  $this->handler->execute('INSERT INTO '.$this->options['table'].' (`key`,`data`,`crc`,`expire`) VALUES (\''.$name.'\',\''.$data.'\',\''.$crc.'\','.$expire.')');
        }
        if($result) {
            if($this->options['length']>0) {    // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        $name   =  MD5($this->options['prefix'].addslashes($name));
        return $this->handler->execute('DELETE FROM `'.$this->options['table'].'` WHERE `key`=\''.$name.'\'');
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->execute('TRUNCATE TABLE `'.$this->options['table'].'`');
    }

}