<?php
class base_hok_trace extends hook {
    protected $options   =  array(
        'TRACE_PAGE_TABS'   => array(
                'BASE'      =>'基本',
                'FILE'      =>'文件',
                'INFO'      =>'流程',
                'ERR|NOTIC' =>'错误',
                'SQL'       =>'SQL',
                'DEBUG'     =>'调试'
        ), // 页面Trace可定制的选项卡
        'PAGE_TRACE_SAVE'   => false,
        'SHOW_PAGE_TRACE'   => false,
    );
    public function run(&$param) {
        if(!IS_AJAX && C('SHOW_PAGE_TRACE')) {
            echo $this->showTrace();
        }
        if(C('PAGE_TRACE_SAVE')){
            $this->saveTrace();
        }
    }

    private function showTrace() {
        $trace  = $this->getTrace();
        // 调用trace页面模板
        ob_start();
        include  BASE_DIR.'/view/trace.php' ;
        $res = ob_get_clean();
        return $res;
    }
    // 保存Trace信息
    private function saveTrace(){
        $trace  = $this->getTrace();
        $array  =   C('TRACE_PAGE_TABS');
        foreach(C('TRACE_PAGE_TABS') as $key=>$val){
            $array[] = $val;
        }
        $content    =   date('[ c ]').' '.get_client_ip().' '.$_SERVER['REQUEST_URI']."\r\n";
        foreach ($trace as $key=>$val){
            if(!isset($array) || in_array($key,$array)) {
                $content    .=  '[ '.$key." ]＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝\r\n";
                if(is_array($val)) {
                    foreach ($val as $k=>$v){
                        $content .= (!is_numeric($k)?$k.':':'').print_r($v,true)."\r\n";
                    }
                }else{
                    $content .= print_r($val,true)."\r\n";
                }
                $content .= "\r\n";
            }
        }
        $dirName= LOG_DIR.'_trace/';
        if(!is_dir($dirName)){
            mkdir($dirName,0755,true);
        }
        error_log( str_replace('<br/>', "\r\n",$content), 3,  $dirName.date('Y-m-d') .'.log' );
    }

    // trace信息
    private function getTrace(){
        // 获取基本信息
        $trace  = array();
        $base   = array(
            '请求信息'  =>  date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']).' '.$_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'].' : '.__URL__,
            '运行时间'  =>  $this->showTime(),
            '吞&nbsp&nbsp吐&nbsp&nbsp率'   =>  number_format(1/G('beginTime','viewEndTime'),2).'req/s',
            '内存开销'  =>  MEMORY_LIMIT_ON?number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024,2).' kb':'不支持',
            '查询信息'  =>  N('db_query').' queries '.N('db_write').' writes ',
            '文件加载'  =>  count(get_included_files()),
            '缓存信息'  =>  N('cache_read').' gets '.N('cache_write').' writes ',
            '配置加载'  =>  count(C()),
            '会话信息'  =>  session_id(),
        );

        // 加载文件信息
        $files  = get_included_files();
        $info   = array();
        foreach($files as $file) {
            $info[] = $file.'  ('.number_format(filesize($file)/1024, 2). 'KB)';
        }

        // 调试信息
        $debug  =   kernel::trace();

        // 调试分类
        $tabs   =   C('TRACE_PAGE_TABS');

        // 遍历
        foreach ($tabs as $name=>$title){
            switch(strtoupper($name)) {
                case 'BASE':                            // 基本信息
                    $trace[$title]  =   $base;
                    break;
                case 'FILE':                            // 文件信息
                    $trace[$title]  =   $info;
                    break;
                default:                                // 调试信息
                    $name       =   strtoupper($name);
                    if( strpos($name,'|') ) {           // 多组信息
                        $array  =   explode('|',$name);
                        $result =   array();
                        foreach($array as $name){ $result += isset($debug[$name]) ? $debug[$name] : array() ; }
                        $trace[$title]  =   $result;
                    }else{
                        $trace[$title]  =   isset($debug[$name]) ? $debug[$name] : '';
                    }
            }
        }
        return $trace;
    }

    // 获取运行时间
    private function showTime() {
        // 显示运行时间
        G('beginTime', $GLOBALS['_begintime']);
        G('viewEndTime');
        // 显示详细运行时间
        return G('beginTime','viewEndTime').'s ( load:'.G('beginTime','loadTime').'s init:'.G('loadTime','initTime').'s exec:'.G('initTime','viewStartTime').'s template:'.G('viewStartTime','viewEndTime').'s )';
    }
}